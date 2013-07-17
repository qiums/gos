// JavaScript Document
var Ads = {
	active_module: '',
	cate_result: {},
	load_cate: false,
	init: function(args){
		jQuery.extend(this, args || {});
		this.mtabs();
		this.setele('all','.pages');
		this.setele('all','.modules');
		this.bind();
		return this;
	},
	mtabs: function(){
		var tabnav = jQuery('<div>',{'class':'tabnav mxtabs'}).append('<div class="clearfix"></div>').appendTo('.catewrap'),
			navul = jQuery('<ul>').prependTo(tabnav);
		$(':checkbox:gt(0)','.modules>.nofloat')
		.each(function(i, ele){
			i = 'tab-'+ele.value;
			jQuery('<li>',{'class':i}).append('<a href="#" rel="'+(ele.value)+'" style="display:none"><span>'+$(ele).next('span').text()+'</span></a>').appendTo(navul);
			jQuery('<div>',{id:i,'class':'tabdiv treedata catepanel'}).bind('tab-function', Ads.cate_data).appendTo('.catewrap');
		});
		$('.catewrap>.tabnav').mxTabs();
	},
	setele: function(key,selector){
		var name=selector.substr(1),id='ele-checkbox-'+name+'-'+key,
		label=$('<label>',{'for':id}).text($.lang[key]);
		$('input:first',selector).clone()
			.attr({id:id, name:name+'['+key+']'}).val(key)
			.prependTo(label)
			.get(0).checked = true;
		label.prependTo(selector+'>.nofloat');
	},
	bind: function(){
		$('#ele-text-category').hide();
		$('a.add,a.edit').dialog({width:800,
			onclose: function(op){
				$('form',this)[0].reset();
			},
			onsuccess: function(op){
				if(!$(op.elem).hasClass('edit')) return ;
				var id=$(op.elem).attr('rel'), key = 'ads-'+id;
				if ('undefined'!=typeof $.datacache.data[key]) return Ads.setdata($.datacache.data[key]);
				$.ajaxPost(Ads.view_url, {id:id},
				function(json){
					$.datacache.data[key] = json;
					Ads.setdata(json);
				});
			},
			buttons: {
				'submit': function(){
					$('form',this).trigger('submit');
				},
				'cancel.cancel': function(){$(this).dialog('close');}
			}});
		$('input[name^=modules]').live('click setmodules', function(){
			return Ads.setmodule(this);
		}).filter(':checked').trigger('setmodules');
		$('input[name^=pages]').live('click setpages', function(){
			return Ads.setpages(this);
		}).filter(':checked').trigger('setpages');
		$('.catename').live('click', Ads.setcate);
		$('#ele-select-adtype').live('change',
		function(){
			var me=this,f='#ele-text-pathname',type=parseInt(this.value,10);
			if (2<=type){
				$(f).parent().clone(true)
					.find('input').removeAttr('id')
					.end()
					.find('label').remove()
					.end().appendTo($(f).closest('li'));
				Ads.bind_upload();
			}else{
				$(f).parent().siblings().remove();
			}
		});
		$('.setpub').bind('click', function(){
			var self=this,pub=$(this).attr('class').match(/published_(\d+)/ig),
			n=Math.abs(parseInt(pub[0].replace('published_',''),10)-1),data={};
			data[this.rel] = {'published': n};
			$.ajaxPost($('form[name=theForm]').attr('action'), {updata:data},
			function(json){
				$(self).removeClass(pub[0]).addClass('published_'+n)
				.attr('title', $.lang['published'][n])
				.closest('tr')[n>0?'removeClass':'addClass']('disabled');
			});
			return false;
		});
		$('button.delete').bind('click', function(){
			$.dialog('<h3>'+$.lang['confirm_delete']+'</h3><p>'+$.lang['caution_delete']+'</p>',
			{type:'questions', elem:this, width:300, buttons:{
				'submit':function(op){
					$.ajaxPost($('form[name=theForm]').attr('action'),
					{'do':'delete',ids:$(op.elem).attr('rel')},
					function(json){
						window.location.reload();
					});
				},'cancel.cancel':function(){return true;}
			}});
		});
		$('form[name=saveForm]').bind('form-pre-serialize',
		function(event, form, options, veto){
			Ads.setcate_value();
		}).saveForm();
		this.bind_upload();
	},
	bind_upload: function(){
		$('.addfile').unbind('click.loadopt')
		.bind('click.loadopt',
		function(){
			Ads.uploader(this);
		});
	},
	uploader: function(self){
		if('undefined'==typeof window['MXUPLOAD_OPTION_ADDFILE']){
			return $.getJSON(this.config_url, {type:'ads', format:'json'},
			function(json){
				window['MXUPLOAD_OPTION_ADDFILE'] = json;
				Ads.uploader(self);
			});
		}
		var options = window['MXUPLOAD_OPTION_ADDFILE'],timer;
		jQuery.extend(options.options.multipart_params,{uid:0});//$.log($(self).prev().val());
		$(self).bind('file-added',
		function(e, up, files){
			$.each(files, function(index, file){
				if (index<1) return true;
				up.removeFile(file);
			});
			$(this).addClass('loading');
			up.start();
		}).bind('file-uploaded',
		function(e, up, file, res){
			$(this).removeClass('loading');
			$(this).prev().val(res.filepath);
		}).mxupload(options);
		$(self).unbind('click.loadopt');
		/*setTimeout(function(){
			$(self).trigger('click')
		},100);*/
	},
	setdata: function(data){
		var key, selector='#adsupe',ex=['pages','modules','position'], exdata={}, ar, self;
		Ads.cate_result = ('all'==data['category']) ? {}:$.to_object(data['category']);
		for (key in Ads.cate_result){
			ar = Ads.cate_result[key];
			if ('string' == (typeof ar).toLowerCase()) Ads.cate_result[key] = ar.split(',');
		}
		for (key in data){
			if (-1!=$.inArray(key, ex)){
				if ('position' == key){
					$('input[name=position][value='+data[key]+']',selector).attr('checked', true);
				}else{
					$('input[name^="'+key+'"]',selector).each(function(){
						this.checked = (-1<data[key].indexOf(this.value));
						$(this).trigger('setmodules').trigger('setpages');
					});
				}
			}else{
				$('[name='+key+']',selector).val(data[key]);
			}
		}
	},
	insert_result: function(ele, doc){
		var img, a=$(doc).find('li.selected>a');
		$(ele).prev().val(a.text());
		if (0>a.text().search(/(jpg|jpeg|gif|png)$/gi)) return ;
		img=new Image();
		img.src = a.prev('img').attr('rel');
		img.onload = function(){
			$('#width').val(this.width);
			$('#height').val(this.height);
		};
	},
	setcate_value: function(){
		var k, selector='li.modules input[name^=modules]',val=[], module=[];
		if ($(selector).filter(':first').is(':checked')){
			val.push('all');
		}else{
			$(selector).filter(':checked')
			.each(function(){
				var cids = [];
				$('div.selected','#tab-'+this.value)
				.each(function(){
					cids.push(parseInt($(this).attr('id').replace('cat_',''),10));
				});
				if (cids.length>0) val.push(this.value+'/'+cids.join(','));
			});
		}
		$('#ele-text-category').val(val.join('/'));
	},
	setpages: function(self){
		if ('all'==self.value){
			var label = $(self).parent().siblings('label');
			label[self.checked?'addClass':'removeClass']('disabled');
			return $(self).closest('.nofloat').find(':checkbox:gt(0)').attr('disabled', self.checked);
		}
	},
	setmodule: function(self){
		var parent=$(self).parent();
		if ('all'==self.value){
			var label = parent.siblings('label'),m;
			if (!self.checked){
				m=!$(self).closest('.nofloat').find(':checked').length?'addClass':'removeClass';
			}else{
				m = 'addClass';
			}
			label[self.checked?'addClass':'removeClass']('disabled');
			$('li.category')[m]('hide');
			return $(self).closest('.nofloat').find(':checkbox:gt(0)').attr('disabled', self.checked);
		}
		var wrap='.catewrap', rs=self.value,
			text=parent.text(),
			li=$('.tab-'+rs), a, rel;
		this.active_module = rs;
		if (!li.length) return ;
		$('li.category:hidden,'+wrap+':hidden').removeClass('hide');
		if (self.checked){
			a=li.find('a').show();
			rel=$('#tab-'+a.attr('rel'));
			if (!rel.length) return ;
			a.mxTabs_change();
		}else{
			$('a',li).hide();
			if(0==$('a:visible',li.parent()).length) return $(wrap).addClass('hide');
			$('a:visible:last',li.parent()).mxTabs_change();
		}
	},
	setcate: function(){
		this.blur();
		var id=$(this).attr('rel').split('-'),mid=id[0],rs;
		if ('undefined' == typeof Ads.cate_result[mid]) Ads.cate_result[mid] = [];
		rs = Ads.cate_result[mid];
		if ($(this).hasClass('selected')){
			$(this).removeClass('selected');
		}else{
			$(this).addClass('selected');
		}
		return false;
	},
	cate_selected: function(mid){
		if ('undefined'==typeof this.cate_result[mid]) return ;
		var k, id;
		for (k in this.cate_result[mid]){
			id = this.cate_result[mid][k];
			$('#tab-'+mid+'>ul>li>#cat_'+id).addClass('selected');
		}
	},
	cate_data: function(event, a){
		var self=this;
		if('undefined'==typeof jQuery.category || $('>ul',self).length>0) return ;
		if (Ads.load_cate) return setTimeout(function(){$(self).trigger('tab-function',[a]);}, 100);
		Ads.load_cate = true;
		if (''==jQuery.category.tmpl){
			jQuery.category.tmpl = '<!--<li class="collaps">'+
			  '<div class="catename" id="cat_[field:id]" rel="[field:mid]-[field:id]" style="border-bottom-width:0">'+
				'<strong>[[field:id]]</strong> <span>[field:catename]</span>'+
				'<em>([field:childs])</em></div>'+
			'</li>-->';
		}
		jQuery.category.handle = jQuery('<ul>').appendTo(this);
		jQuery.category.mid = $(a).attr('rel');
		jQuery.category.geturl = Ads.cate_url;
		jQuery.category.get(0);
		jQuery.category.after_get = function(){
			Ads.cate_selected(jQuery.category.mid);
			Ads.load_cate = false;
			$('em.hitarea').remove();
		}
	},
	get: function(){
		if(!$.G.mod || !$.G.ac || !$('.ad').length) return ;
		var d={};
		d.pages = ('main'==$.G.mod && $.G.ac=='index')?'main':$.G.ac;
		if($.G.mid) d.mid=$.G.mid;
		if($.G.catenode) d.cid=$.G.catenode;
		$.getJSON($.G.webroot+'?app=ads&ac=get&format=json', d, Ads.build);
	},
	build: function(d){
		var a={};
		$.each(d,function(i,b){
			if ('undefined'==typeof a[b.position]) a[b.position]=[];
			a[b.position].push(b);
		});
		$('.ad').each(function(){
			var me=this,c=$.trim($(me).attr('class').replace('ad',''));
			if('undefined'==typeof a[c]){
				$(me).remove();
				return true;
			}
			$.each(a[c],function(i,b){
				if(null==b) return true;
				var img=$('<img>',{src:b.fileurl});
				img.appendTo(me);
				if(''!=b.clickurl) img.wrap('<a href="'+b.clickurl+'" target="_blank"></a>');
				delete a[c][i];
			});
		});
	}
};