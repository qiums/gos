// JavaScript Document
(function($){
	$.comment = function(el, op){
		if (!op.url || !op.mid || !op.aid) return ;
		var box = $('#cmtboxes', el)
			, input = $('#cmtinput', el);
		if (!box.length || !input.length) return ;
		op.size = Math.min(20, op.size || 20);
		this.op = op;
		var ul = $('ul', box), data;
		data = {mid:op.mid, aid:op.aid, limit:op.size};
		if (op.type === 'html'){
			box.empty().load(op.url, data);
		}else{
			var tmpl = ul.html().replace(/^(<!\-\-)|((\/\/)*\-\->)$/g, '');
			$.post(op.url, data, render, op.type || 'json');
		}
		
		var render = function(res){
		}
	};
	$.comment.init = function(){
		var me = this;
		$('a.comment-map').on('click.movetoelem',
		function(){$('html,body').animate({scrollTop:$(me.el).offset().top}, 1000);
			return false;
		});
		//this.box.delegate('.reply', 'click.reply-cmt',
	};
	$.fn.comment = function(){
		return this.each(function(){
			$.comment(this, $(this).data() || {}).init();
		});
	};
	if ('undefined' !== typeof $.iscroll){
		$('[data-trigger="comment"]').iscroll('gt',
		function(){
			$(this).comment();
		});
	};
var comment={
	get: function(p){
		if('number'==$.type(p) || -1!=p.search('/^\d+$/g')){
			$.G.cmt.page = p;
		}else{
			//$('.cmtinput').iscroll();
			$('.cmtuserlogin a').unbind('click')
			.bind('click', function(){
				var href=$(this).attr('href'),ac=href.substr(href.lastIndexOf('#')+1);
				if('function'==typeof common.userlogin){
					return common.userlogin(ac);
				}
				return false;
			});
			$('form[name=replyForm],form[name=postForm]').each(
			function(){
				$(this).saveForm(comment.submit);
			});
		}
		$('.cmtlist').loadhtml($.G.webpath+'components/comment/tree',comment.bind,$.G.cmt);
		return false;
	},
	bind: function(){
		delete $.G.cmt.page;
	},
	reply:function(t){
		$('<div id="reply-comment-tmp" class="hide" />')
			.insertBefore('#reply-comment-div')
			.bind('comment-input-cleanup', function(){
				$(this).replaceWith($('#reply-comment-div'));
			});
		$('#reply-comment-div')
			.insertAfter($(t).parent().get(0))
			.find('input[name=pid]').val(t.rel).end()
			.find('textarea').get(0).focus();
	},
	submit: function(s){
		if(s.error) return s;
		this.form[0].reset();
		if(this.form.attr('name')=='postForm'){
			$('a.comment').trigger('click.movetoelem');
			comment.get(1);
		}else{
			var ul = $(this.form).parent().siblings('ul');
			if(!ul.length) ul=$('<ul></ul>').appendTo($(this.form).closest('li'));
			$.event.trigger('comment-input-cleanup');
			$('<li></li>',{'class':'subli'}).prependTo(ul).loadhtml($.G.webpath+'components/comment/getone?id='+s.ok);
		}
	},
	init: function(){
		$('.ccqinput','#comment').live('click',function(){
			$.event.trigger('comment-input-cleanup');
			return false;
		});
		$('textarea[name=context]','#comment').live('blur',function(){
			if ($(this).hasClass('sampletext')) return ;
			if (this.value=='') $.event.trigger('comment-input-cleanup');
		}).live('keyup',function(event){
			if(event.ctrlKey && event.keyCode==13) return comment.submit.call($(this).closest('form'));
		});
		$('a.reply','#comment').live('click', function(){
			comment.reply(this);
			return false;
		});
		$('a','.cmtlist .pagination_bar').live('click', function(){
			$('a.comment').trigger('click.movetoelem');
			return comment.get(parseInt(this.rel,10));
		});
	}
};
})(jQuery);