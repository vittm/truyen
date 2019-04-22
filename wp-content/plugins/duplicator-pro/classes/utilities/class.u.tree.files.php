<?php
defined("ABSPATH") or die("");

/**
 * Utility class to create a tree structure from paths
 *
 * Standard: PSR-2
 * @link http://www.php-fig.org/psr/psr-2
 *
 * @package DUP_PRO
 * @subpackage classes/utilities
 * @copyright (c) 2017, Snapcreek LLC
 * @license	https://opensource.org/licenses/GPL-3.0 GNU Public License
 * @since 3.8.1
 *
 */

/**
 * Tree files utility
 * 
 */
class DUP_PRO_Tree_files
{
    /**
     * All props must be public for json encode
     */
    /**
     *
     * @var DUP_PRO_Tree_files_node
     */
    public $tree = null;

    /**
     *
     * @var string root tree path
     */
    public $rootPath = '';

    /**
     *
     * @param string $rootPath
     */
    public function __construct($rootPath)
    {
        $rootPath       = rtrim(str_replace("\\", '/', $rootPath), '/');
        $this->tree     = new DUP_PRO_Tree_files_node($rootPath);
        $this->tree->addAllChilds();
        $this->rootPath = $rootPath;
    }

    /**
     *
     * @param string $path full path
     * @param type $data optiona data associated at node
     * @return boolean|DUP_PRO_Tree_files_node
     */
    public function addElement($path, $data = array())
    {
        $newElem = $this->tree->addChild($path, true, true);
        if ($newElem) {
            $newElem->data = $data;
        }
        return $newElem;
    }
}

/**
 * Tree node data
 */
class DUP_PRO_Tree_files_node
{
    const MAX_CHILDREN_PER_FOLDER = 100;

    /**
     * All props must be public for json encode
     */
    /**
     *
     * @var string unique id l0_l1_l2....
     */
    public $id = '';

    /**
     *
     * @var string parent id l0_l1_...
     */
    public $parentId = '';

    /**
     *
     * @var string file basename
     */
    public $name = '';

    /**
     *
     * @var string full path
     */
    public $fullPath = '';

    /**
     *
     * @var bool is directory
     */
    public $isDir = false;

    /**
     *
     * @var DUP_PRO_Tree_files_node[] childs nodes
     */
    public $childs = array();

    /**
     *
     * @var array optiona data associated ad node
     */
    public $data = array();

    /**
     *
     * @var bool true if folder have a childs 
     */
    public $haveChildren = null;

    /**
     *
     * @var bool
     */
    private $traversed = false;

    /**
     *
     * @param string $path // file path
     * @param string $id // current level unique id
     * @param string $parent_id // parent id
     */
    public function __construct($path, $id = '0', $parent_id = '')
    {
        $path           = rtrim(str_replace("\\", '/', $path), '/');
        $this->id       = (strlen($parent_id) == 0 ? '' : $parent_id.'_').$id;
        $this->parentId = $parent_id;
        $this->name     = basename($path);
        $this->fullPath = $path;
        $this->isDir    = is_dir($this->fullPath);
        $this->haveChildrenCheck();
    }

    /**
     * create tree tructure until at basename
     *
     * @param string $path file path
     * @param bool $fullPath if true is considered a full path and must be a child of a current node else is a relative path
     * @param bool $loadTraverse if true, add the files and folders present at the level of each node
     * @return boolean|DUP_PRO_Tree_files_node if fails terurn false ellse return the leaf child added
     * @throws Exception
     */
    public function addChild($path, $fullPath = true, $loadTraverse = false)
    {
        if (empty($path)) {
            return false;
        }

        $path = rtrim(str_replace("\\", '/', $path), '/');

        if ($fullPath) {
            if (strpos($path, $this->fullPath) !== 0) {
                throw new Exception('Can\'t add no child on tree; file: "'.$path.'" || fullpath: "'.$this->fullPath.'"');
            }
            $child_path = substr($path, strlen($this->fullPath));
        } else {
            $child_path = $path;
        }

        $tree_list = explode('/', $child_path);

        if (empty($tree_list[0])) {
            array_shift($tree_list);
        }

        if (!array_key_exists($tree_list[0], $this->childs)) {
            $childPath = $this->fullPath.'/'.$tree_list[0];
            $child     = new DUP_PRO_Tree_files_node($childPath, count($this->childs), $this->id);

            $this->childs[$tree_list[0]] = $child;
        } else {
            $child = $this->childs[$tree_list[0]];
        }

        if ($loadTraverse) {
            $child->addAllChilds();
        }

        if (count($tree_list) > 1) {
            array_shift($tree_list);
            return $child->addChild(implode('/', $tree_list), false, $loadTraverse);
        } else {
            return $child;
        }
    }

    /**
     * if is dir scan all children files and add on childs list
     */
    public function addAllChilds()
    {
        if ($this->traversed === false) {
            $this->traversed = true;

            if ($this->isDir && ($childs = @scandir($this->fullPath)) !== false) {
                $childs = array_slice($childs, 2, self::MAX_CHILDREN_PER_FOLDER);

                foreach ($childs as $child_name) {
                    if (!isset($this->childs[$child_name])) {
                        $childPath                 = $this->fullPath.'/'.$child_name;
                        $this->childs[$child_name] = new DUP_PRO_Tree_files_node($childPath, count($this->childs), $this->id);
                    }
                }
            }
        }
    }

    /**
     * check if current dir have children without load nodes
     */
    private function haveChildrenCheck()
    {
        if ($this->isDir) {
            $this->haveChildren = false;

            if ($dh = opendir($this->fullPath)) {
                while (!$this->haveChildren && ($file = readdir($dh)) !== false) {
                    $this->haveChildren = $file !== "." && $file !== "..";
                }
                closedir($dh);
            }
        }
    }

    /**
     * sort child list with callback function
     *
     * @param callable $value_compare_func
     * @return void
     */
    public function uasort($value_compare_func)
    {
        if (!is_callable($value_compare_func)) {
            return;
        }

        foreach ($this->childs as $child) {
            $child->uasort($value_compare_func);
        }

        uasort($this->childs, $value_compare_func);
    }

    /**
     * traverse tree anche call callback function
     *
     * @param callable $callback
     * @return type
     */
    public function treeTraverseCallback($callback)
    {
        if (!is_callable($callback)) {
            return;
        }

        foreach ($this->childs as $child) {
            $child->treeTraverseCallback($callback);
        }

        call_user_func($callback, $this);
    }
}
