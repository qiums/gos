// JavaScript Document
var Attach = {
	list_size: 12,
	more: 1,
	init: function(args){
		jQuery.extend(this, args || {});
		this.bind();
		//this.get();
		return this;
	},
	bind: function(){
		$('#arc-attach-list').bind('tab-function', function(){
			return Attach.get($(this).find('.attach-list').removeClass('hide'));
		}).trigger('tab-function');
		$('#upload-panel').bind('tab-function', function(){
			return Attach.set_upload_frame(this);
		});
		$('a.insertfile').live('click', function(){
			this.blur();
			var pa = $(this).parent();
			if ('single'==Attach.choose) pa.siblings('.selected').removeClass('selected');
			if (pa.hasClass('selected')){
				pa.removeClass('selected');
			}else{
				pa.addClass('selected');
			}
			return false;
		});
		$('.getmore').live('click', function(){
			this.blur();
			return Attach.get($('#arc-attach-list>.attach-list'), 'more');
		});
	},
	set_upload_frame: function(div){
		var frame = $(div).find('iframe');
		if (!frame.hasClass('hide')) return ;
		frame.removeClass('hide').attr('src', this.upload_frame_url);
		jQuery.add_event(frame[0], 'load',function(){
			this.contentWindow['Upload_finish'] = Attach.upload_finish;
		});
	},
	get: function(panel, act){
		if (panel.find('ul>li').length>0 && 'more'!=act) return false;
		if (1 == this.more){
			$('.getmore').show();
			panel.find('ul').remove();
		}
		$.get(this.get_list_url, {'page':this.more, size:this.list_size}, function(data){
			Attach.more++;
			Attach.set_html(panel, data);
		});
		return false;
	},
	upload_finish: function(op, rs){
		var k, ext, bname, li='';
		for (k in rs){
			bname = rs[k]['url'];
			ext = bname.substr(bname.lastIndexOf('.')+1);
			li += '<li class="new"><img src="'+JS_PATH+'filetype/'+ext+'.gif" rel="'+bname+'" align="absmiddle" /> <a href="#" title="'+bname+'" rel="0|'+ext+'" class="insertfile">'+bname+'</a></li>';
		}
		X.change_tab($('.arc-attach-dialog>ul.tabnav>.tab-attach-list>a')[0]);
		Attach.set_html($('#arc-attach-list>.attach-list'), li, 'prepend');
	},
	set_html: function(panel, data, m){
		if (!$.trim(data)){
			this.more = 1;
			return $('.getmore').hide();
		}
		if (!panel.find('ul').length){
			$('<ul></ul>').append(data).prependTo(panel);
		}else{
			('prepend'==m) ? panel.find('ul').prepend(data) : panel.find('ul').append(data);
		}
		if (!m && $(data).filter('li').length < Attach.list_size) $('.getmore').hide();
	}
};