$.mxupload = {
	selector: null,
	error: [],
	result: [],
	reset: function(){
		if(null==this.selector || undefined==jQuery.fn.pluploadQueue) return ;
		this.selector.pluploadQueue(this.options);
		this.setsize();
	},
	setsize: function(){
		setTimeout(function(){
			$('.plupload_usesize').text(plupload.formatSize($.mxupload.usesize));
		}, 500);
	}
};
$.fn.mxupload = function(options){
	var self=this, runtimes='html5', oberr, reader, id=self.attr('id');
	$.mxupload.selector=self;
	try{
		reader = new FileReader();
		reader = null;
	}catch(e){
		runtimes = 'flash';
	};
	if(options.lang){
		$.mxupload.lang = options.lang;
		if('string'==$.type($.mxupload.lang))
			$.mxupload.lang = eval('('+$.mxupload.lang+')');
		delete options.lang;
	}
	if(options.options){
		options = options.options;
		if('string'==$.type(options)) options = eval('('+options+')');
	}
	if('undefined'==typeof plupload){
		if ((options.plqueue)&&undefined==jQuery.fn.pluploadQueue){
			$.include($.G.realroot+'app/file/plupload/jquery.plupload.queue/css/jquery.plupload.queue.css');
			return $.getScript($.G.realroot+'app/file/plupload/jquery.plupload.queue/jquery.plupload.queue.js',
			function(){self.mxupload(options);});
		}
		return $.getScript($.G.realroot+'app/file/plupload/plupload.js',function(){
			$.getScript($.G.realroot+'app/file/plupload/plupload.'+runtimes+'.js',
			function(){self.mxupload(options);});
		});
	}
	if('undefined'==typeof $.mxupload.usesize) $.mxupload.usesize = options.use_size;
	if (!id){
		id = 'plupload-'+new Date().getTime();
		self.attr('id', id);
	}
	options.browse_button = id;
	options = jQuery.extend({
		runtimes: runtimes,
		flash_swf_url : $.G.realroot+'app/file/plupload/plupload.flash.swf',
		resize : {width : 1000, height : 1000, quality : 90},
		init:{
			Error: function(up, err){
				var message = '<div>- '+err.message+' <em style="color:#999">('+err.file.name+')</em></div>';
				if(oberr){
					$('>div:first', oberr).append(message);
					oberr.dialog('position');
				}else{
					oberr = $.dialog('<strong>'+$.mxupload.lang['Files queue error']+':</strong><br /><br />'+message,
						{type:'warning',reload:0,name: 'fileadd-error',
						buttons:{
							'continue':function(){
								$(this).dialog('destroy');
								oberr = null;
							}
						}});
				}
				return false;
			},
			FilesAdded: function(up, files){
				if(false===$(self).triggerHandler('file-added',[up, files])) return ;
				$.each(files, function(index, file){
					$.mxupload.usesize+=file.size;
					if(-1==$.mxupload.options.over_size) return true;
					$.mxupload.options.over_size-=file.size;
					if($.mxupload.options.over_size<0){
						$.mxupload.options.init.Error(up, {message: $.mxupload.lang['No enough free space.'], file: file});
						up.removeFile(file);
					}
				});
			},
			FilesRemoved: function(up, files){
				$.each(files, function(index, file){
					$.mxupload.usesize-=file.size;
					if(-1==$.mxupload.options.over_size) return true;
					$.mxupload.options.over_size+=file.size;
				});
			},
			QueueChanged: function(up){
				self.trigger('queue-changed', [$.mxupload.error]);
			},
			UploadProgress: function(up, file){
				self.trigger('upload-progress', [up,file]);
			},
			FileUploaded: function(up, file, info){
				if(info.response){
					info.response = eval('('+info.response+')');
					if(104==parseInt(info.response.code,10)) up.stop();
				}
				$.mxupload.result.push([file, info]);
				self.trigger('file-uploaded', [up, file, info.response]);
			},
			UploadComplete: function(up, files){
				self.trigger('upload-complete', [up, files]);
				$.mxupload.reset();//$.mxupload.result = [];
			}
		}
	}, options||{});
	$.mxupload.options = options;
	plupload.addI18n($.mxupload.lang);
	if (options.plqueue){
		delete options.browse_button;
		self.pluploadQueue(options);
		return $.mxupload.setsize();
	}
	var uploader = new plupload.Uploader(options);
	uploader.init();
	return uploader;
};
