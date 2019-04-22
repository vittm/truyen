(function () {
    tinymce.PluginManager.add('cctab_mce_button', function (editor, url) {
        editor.addButton('cctab_mce_button', {
            icon: ' cctab-icon',
            text: 'CCTAB',
            tooltip: 'CCTAB ShortCode',
            type: 'menubutton',
            //minWidth: 210,
            menu: [
			
// Call to action start here

{
	text: 'Call To Action Link',
	onclick: function () {
		editor.windowManager.open({
			title: 'Affiliate Call To Action Link',
			body: [
				{
					type: 'listbox',
					name: 'textColor',
					label: 'Color (Select Text Color):',
					'values': [
						{text: 'Orange', value: 'orange'},
						{text: 'Red', value: 'red'},
						{text: 'Blue', value: 'blue'},
						{text: 'Green', value: 'green'}
					]
				},
				{
					type: 'textbox',
					name: 'TargetUrl',
					label: 'Affiliate Llink: ',
					minWidth: 300,
					value: 'http://'
				},
				{
					type: 'textbox',
					name: 'anchorText',
					label: 'Anchor Text: ',
					minWidth: 300,
					value: ' '
				}
			],
			onsubmit: function (e) {
				editor.insertContent('[cta_link \n\
						link="' + e.data.TargetUrl + '" \n\
						color="' + e.data.textColor + '" \n\
			  ]' + e.data.anchorText + '[/cta_link]');
			}
		});
	}
},

{
	text: 'Call To Action Button',
	onclick: function () {
		editor.windowManager.open({
			title: 'Affiliate Call To Action Button Link',
			body: [
				{
					type: 'listbox',
					name: 'buttonColor',
					label: 'Color (Select Contectual Color):',
					'values': [
						{text: 'warning', value: 'warning'},
						{text: 'default', value: 'default'},
						{text: 'primary', value: 'primary'},
						{text: 'success', value: 'success'},
						{text: 'info', value: 'info'},
						{text: 'danger', value: 'danger'},
						{text: 'link', value: 'link'}
					]
				},
				{
					type: 'listbox',
					name: 'ButtonSize',
					label: 'Size: ',
					'values': [
						{text: 'Medium', value: ''},
						{text: 'Big', value: 'lg'},
						{text: 'Small', value: 'sm'},
						{text: 'Extra Small', value: 'xs'}
					]
				},
				{
					type: 'textbox',
					name: 'TargetUrl',
					label: 'Affiliate Llink: ',
					minWidth: 300,
					value: 'http://'
				},
				{
					type: 'textbox',
					name: 'ButtonText',
					label: 'Button Text: ',
					value: ''
				}

			],
			onsubmit: function (e) {
				editor.insertContent('[cta_btn \n\
						color="' + e.data.buttonColor + '"  \n\
						size="' + e.data.ButtonSize + '"  \n\
						link="' + e.data.TargetUrl + '" \n\
			  ]' + e.data.ButtonText + '[/cta_btn]');
			}
		});
	}
},
{
	text: 'Call To Action Image Box: Type 01',
	onclick: function () {
		editor.windowManager.open({
			title: 'Affiliate Image Box Call To Action',
			body: [
				{
					type: 'textbox',
					name: 'imageSource',
					label: 'Image Source: ',
					value: 'http://'
				},
				{
					type: 'textbox',
					name: 'imageAlt',
					label: 'Image Alt Tag: ',
					value: ''
				},
				{
					type: 'listbox',
					name: 'boxSize',
					label: 'Image Container Size :',
					'values': [
						{text: 'Medium', value: '6'},
						{text: 'Full Width', value: '12'},
						{text: 'Small', value: '4'}
					]
				},
				{
					type: 'listbox',
					name: 'boxFloat',
					label: 'Position :',
					'values': [
						{text: 'Right', value: 'pull-right'},
						{text: 'Left', value: 'pull-left'},
						{text: 'Center', value: 'center-block'}
					]
				},                                        
				{
					type: 'textbox',
					name: 'ButtonText',
					label: 'Button Text: ',
					value: ''
				},
				{
					type: 'textbox',
					name: 'TargetUrl',
					label: 'Affiliate Llink: ',
					minWidth: 300,
					value: 'http://'
				},
				{
					type: 'listbox',
					name: 'buttonColor',
					label: 'Button Color (Select Contextual Color):',
					'values': [
						{text: 'warning', value: 'warning'},
						{text: 'default', value: 'default'},
						{text: 'primary', value: 'primary'},
						{text: 'success', value: 'success'},
						{text: 'info', value: 'info'},
						{text: 'danger', value: 'danger'},
						{text: 'link', value: 'link'}
					]
				},
				{
					type: 'listbox',
					name: 'ButtonSize',
					label: 'Button Size: ',
					'values': [
						{text: 'Medium', value: ''},
						{text: 'Big', value: 'lg'},
						{text: 'Small', value: 'sm'},
						{text: 'Extra Small', value: 'xs'}
					]
				}

			],
			onsubmit: function (e) {
				editor.insertContent('[cta_image_box1\n\
					imagesrc="' + e.data.imageSource + '"\n\
					alt="' + e.data.imageAlt + '"\n\
					boxsize="' + e.data.boxSize + '"\n\
					align="' + e.data.boxFloat + '"\n\
					link="' + e.data.TargetUrl + '"\n\
					color="' + e.data.buttonColor + '"\n\
					btnsize="' + e.data.ButtonSize + '"\n\
					btntext="' + e.data.ButtonText + '" \n\
				]');
			}
		});
	}
},

{// call to action image box type 02
	text: 'Call To Action Image Box - Type 02',
	onclick: function () {
		editor.windowManager.open({
			title: 'Affiliate Image Box Call To Action',
			body: [
				{
					type: 'textbox',
					name: 'imageSource',
					label: 'Image Source: ',
					value: 'http://'
				},
				{
					type: 'textbox',
					name: 'imageAlt',
					label: 'Image Alt Tag: ',
					value: ''
				},
				{
					type: 'textbox',
					name: 'TargetUrl',
					label: 'Affiliate Llink: ',
					minWidth: 300,
					value: 'http://'
				},
				  
				{
					type: 'textbox',
					name: 'ButtonText',
					label: 'Button Text: ',
					value: ''
				},
				{
					type: 'listbox',
					name: 'buttonColor',
					label: 'Button Color (Select Contextual Color):',
					'values': [
						{text: 'warning', value: 'warning'},
						{text: 'default', value: 'default'},
						{text: 'primary', value: 'primary'},
						{text: 'success', value: 'success'},
						{text: 'info', value: 'info'},
						{text: 'danger', value: 'danger'},
						{text: 'link', value: 'link'}
					]
				},
				{
					type: 'listbox',
					name: 'ButtonSize',
					label: 'Button Size: ',
					'values': [
						{text: 'Medium', value: ''},
						{text: 'Big', value: 'lg'},
						{text: 'Small', value: 'sm'},
						{text: 'Extra Small', value: 'xs'}
					]
				},
				{
					type: 'textbox',
					name: 'HeadLine',
					label: 'Headline: ',
					value: ''
				},
				{
					type: 'listbox',
					name: 'TitleColor',
					label: 'Color (Select Text Color):',
					'values': [
						{text: 'Orange', value: 'orange'},
						{text: 'Red', value: 'red'},
						{text: 'Blue', value: 'blue'},
						{text: 'Green', value: 'green'}
					]
				},				
				{
					type: 'textbox',
					name: 'BoxTextContent',
					label: 'Box Text Content: ',
					value: '',
					multiline: true,
					minWidth: 300,
					minHeight: 100
				}

			],
			onsubmit: function (e) {
				editor.insertContent('[cta_image_box2  \n\
						imagesrc="' + e.data.imageSource + ' "\n\
						alt="' + e.data.imageAlt + '" \n\
						link="' + e.data.TargetUrl + '" \n\
						btntext="' + e.data.ButtonText + '" \n\
						color="' + e.data.buttonColor + '" \n\
						btnsize="' + e.data.ButtonSize + '"  \n\
						headline="' + e.data.HeadLine + '" \n\
						titlecolor="' + e.data.TitleColor + '"]' + e.data.BoxTextContent + '[/cta_image_box2]' );
			}
		});
	}
},

{// call to action image box type 02
	text: 'Full Width Image',
	onclick: function () {
		editor.windowManager.open({
			title: 'Full Width Image',
			body: [
				{
					type: 'textbox',
					name: 'imageSource',
					label: 'Image Source: ',
					value: 'http://'
				},
				{
					type: 'textbox',
					name: 'imageAlt',
					label: 'Image Alt Tag: ',
					value: ''
				},
				{
					type: 'textbox',
					name: 'TargetUrl',
					label: 'Affiliate Llink: ',
					minWidth: 300,
					value: 'http://'
				}
			],
			onsubmit: function (e) {
				editor.insertContent('[image_ext_width  \n\
						imagesrc="' + e.data.imageSource + ' "\n\
						alt="' + e.data.imageAlt + '" \n\
						link="' + e.data.TargetUrl + '"]' );
			}
		});
	}
}

               
                /// ### Call TO ACTION END HERE

/*//// ######################################## 
 * Dont EDITE below this line  ///////////////
 * ##############################################*/

            ]
        });
    });
})();