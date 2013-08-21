// JavaScript Document
(function($){
	var user = function(expr){
		var cs = function(res){
				var op = $(this).data();
				if (!op.logined) return ;
				$.get(op.ckurl, {'do':'check'}, function(res){
					if (!res.code) return ;
					$('.login',expr).html(res.body.command);
					$('p', expr).find('a').remove();
					$('p', expr).append(res.body.username);
				});
			}
			, opend = function(e){
				jQuery.doane(e);
				$(this).dialog({
					width:520, id:'login-box',
					onRender: function(){
						$('div.tab-content>div', this.boxes).addClass('tab-pane');
						var me = this
							, fn = function(){ me.hide(); };
						cmd_login($('form[name=loginForm]', this.boxes), true, fn);
						cmd_signup($('form[name=signupForm]', this.boxes), fn);
					}
				});
			};
			$(expr)
				.bind('check-status', cs)
				.on('click.open-dialog', '.gologin', opend)
				.trigger('check-status');
	}, d = function(res, fn){
		$.isFunction(fn) && fn();
		$.dialog('<strong>'+(res.message)+'</strong>', {width:350, buttons:res.body});
	}, cmd_login = function(form, fd, fn){
		fd = fd || false;
		form.data('login_fail',0)
		.saveForm(function(res){
			if (1 !== res.code){
				var fail = $(this.formele).data('login_fail');
				if (fail>=3) captcha($('div.plus-remember',this.formele));
				$(this.formele).data('login_fail', fail+1);
				return false;
			}
			d(res, fn);
			$('#toplogin').data('logined', 1).trigger('check-status');
			$.event.trigger('load-has-user');
		});
		if (!fd && !form.length) user('#toplogin');
	}, cmd_signup = function(form, fn){
		$('#reg-text-username').one('focus.getcaptcha',
		function(){
			captcha($('div.formbtn',form));
		});
		form.saveForm(function(res){
			d(res, fn);
		});
	}, cmd_find = function(form, fn){
		form.saveForm(function(res){
			d(res, fn);
		});
	}, captcha = function(b){
		$('[class$=captcha]', b.closest('form')).remove();
		var li=$('<div>',{'class':'formline plus-captcha'}).append('<label for="qc-ele-captcha">Captcha?</label>'),
			ele=$('<input />',{id:'qc-ele-captcha', name:'captcha',type:'text','class':'req-string minlength4',maxlength:4})
				.data('alt', 'Please enter captcha.')
				.appendTo(li);
		ele.one('focus.showcaptcha',
		function(){
			var src = $.G.captchaurl,
			span=$('<img>',{src:src})
				.wrap('<span class="captchabox"></span>').parent();
			$('<a href="#">Refresh</a>').bind('click.recaptcha',
			function(){
				$(this).siblings('img').attr('src', src+(src.indexOf('?')==-1?'?':'&')+Math.random());
				ele[0].focus();
				return false;
			}).appendTo(span);
			span.insertAfter(this);
		})/*.on('blur.checkcaptcha',
		function(){
			var me=this;
			$.post($.G.captchaurl, {val:$(this).val()},
			function(res){
				$(me).next('.captcha-status').remove();
				$('<i class="captcha-status" />').addClass('icon-' + (res.code>0 ? 'ok':'remove')).insertAfter(me);
			});
		}).bind('element-build', function(e, res){
			if (res){
				return $(this).next('.captcha-status-1').length>0;
			}
			return res;
		})*/;
		li.insertBefore(b);
	};
	$(function(){
		cmd_login($('form[name=loginForm]'));
		cmd_signup($('form[name=signupForm]'));
		$.event.trigger('load-has-user');
	});
})(jQuery);
function signup(s){
	if (s.error){
		captcha.call($('#ele-text-username')[0]);
		return s;
	}
	$('.regfb').prev().hide()
		.end().replaceWith('<fieldset>'
			+'<legend>{$lang["register_success"]}</legend>'
			+'<div class="divtip"><p>'+s.ok+'</p>{if $module_conf[special_group]}<p>{$crmail}</p>{/if}<p><a href="{echo url("member")}">&laquo; Jump to user center</p></div>'
			+'</fieldset>');
	return false;
};
function resetpass(s){
	if (s.error){
		captcha.call($('#ele-find-username')[0]);
		return s;
	}
	/*{if 'reset'==$do}*/
	$('.findfb').prev().hide()
		.end().replaceWith('<fieldset>'
			+'<legend>{$lang["resetpass_finish"]}</legend>'
			+'<div class="divtip"><p>'+s.ok+'</p><p><a href="{echo url("member")}">Login now!</a></p></div>'
			+'</fieldset>');
	/*{else}*/
	$('.findfb').prev().hide()
		.end().replaceWith('<fieldset>'
			+'<legend>{$lang["resetpass_request_success"]}</legend>'
			+'<div class="divtip"><p>'+s.ok+'</p><p>{$crmail}</p></div>'
			+'</fieldset>');/*{/if}*/
	return false;
};
function ckfield(form){
	var c=true,rs={formhash:'{$formhash}'};
	$('[name=username],[name=email]',form)
	.each(function(){
		rs[$(this).attr('name')] = $.trim(this.value);
		$(this).parent().addClass('loading');
	});
	$.ajax({
		url:'{echo url("ac=check")}',
		type:'post',data: rs,dataType:'json',async:false,
		success:function(json){
			$('li.loading',form).removeClass('loading');
			c = 'undefined'==typeof json.error;
			if(json.error){
				form.trigger('form-check-error',[json.elem, json.error]);
			}
		}
	});
	return c;
};
function check(a,form,op){
	if (false===$(form).ckForm()) return false;
	var me=this,b=$('button',form);
	if(!ckfield(form)) return false;
	op.obload = $.dialog({type:'load',name:'form-loading'});
	b.attr('disabled', true);
};
function run_menu(hash){
	var ob='ul.navbox';
	if (hash) hash=$('#d'+hash.substr(1),ob);
	if (!hash.length) hash=$('li:first',ob);
	hash.siblings().removeClass('active')
		.end().addClass('active');
	$('.divblk','.sidemain').addClass('hide')
		.filter('.'+hash.attr('id')).removeClass('hide');
	return false;
}
$(function(){
	/*$(document).on('focus.getcaptcha', '#ele-reg-username,#ele-find-email', captcha);
	$('form[name=theForm]').saveForm(login);
	$('form[name=findForm]').saveForm({
		success:resetpass
	});
	$('form[name=regForm]').saveForm({
		beforeSubmit:check,
		success:signup
	});
	$(document).on('click', '.navbox a',
	function(){
		var href=$(this).attr('href');
		return run_menu(href.substr(href.lastIndexOf('#')));
	});*/
});
