$.apptree = {
	handle: null,
	cache:{},
	//datacache:{},
	id:0, tmpl:'',
	get: function(id){
		id = id || 0;
		this.id = id;
		if ('undefined'!=typeof this.cache[this.id]) return this.show();
		if (null != this.handle) this.handle.parent().addClass('loading');
		$.ajax({
			type: 'post',
			url: jQuery.apptree.geturl,
			data: 'id='+id+'&tid='+this.tid+'&format=json',
			dataType: 'json',
			success: function(data){
				if(''==data||!data) return ;
				jQuery.apptree.cache[jQuery.apptree.id] = data;
				jQuery.apptree.show();
			}
		});
	},
	show: function(){
		if (!this.cache[this.id]) return ;
		if ('undefined'==typeof $.replace_tmpl){
			this.tmpl = $(this.expr).html();
			return $.getScript($.G.pubdir+'scripts/jquery.tmpl.js',
			function(){
				jQuery.apptree.show();
			});
		}
		if (null != this.handle){
			this.handle.parent().removeClass('loading');
		}else{
			this.handle = $(this.expr);
		}
		this.handle.append($.replace_tmpl(this.tmpl, this.cache[this.id]));
		$('>li', this.handle).each(function(){
			if(parseInt($('div>em',this).text().replace(/\(|\)/ig,''))>0)
				jQuery('<ul>').hide().appendTo(this);
		});
		this.tree();
		if ('function' == typeof this.after_get){
			if (false === this.after_get()) return false;
		}
	},
	tree:function(handle){
		handle = handle || this.handle;
		$('small.disabled', handle).removeClass('disabled');
		handle.find('>li')
			.removeClass('last lastExpand lastCollaps')
			.filter(":last-child")
				.addClass('last')
			.end()
			.filter(':has(>ul:hidden)')
				.replaceClass('collaps','expand')
				.replaceClass('last','lastExpand')
			.end()
			.not(':has(>ul:hidden)')
				.replaceClass('expand','collaps')
				.replaceClass('last', 'lastCollaps')
			.end()
			.not(':has(>ul)')
				.removeAttr('class')
			.end()
			.filter(':not(>ul):last-child')
				.addClass('last')
			.end()
			.find('.hitarea').remove()
			.end()
			.filter(':has(>ul)')
			.prepend('<em class="hitarea" />')
				.find('em.hitarea').unbind('click')
				.bind('click', function(){
					jQuery.apptree.tree_trigger(this);
				});
		$('li:first',handle).find('.arrow_up>small').addClass('disabled');
		$('li:last',handle).find('.arrow_down>small').addClass('disabled');

	},
	tree_trigger: function(t){
		this.handle = $(t).parent()
			.swapClass('collaps','expand')
			.swapClass('lastCollaps','lastExpand')
			.find('ul').toggle();
		if (this.handle.find('li').length==0){
			var id = $(t).siblings('div').attr('id');
			this.get(id.substr(id.lastIndexOf('_')+1));
		}
	},
	move:function(t,a){
		//if (X.nochecked()) return false;
		if ('undefined'!=typeof a.id) $('form#theForm').prepend('<input type="hidden" name="ids[]" id="insert_ids" value="'+a.id+'" />');
		var fn = {};
		fn.submit=function(rs,op){
			if (!rs) return Prompt.close(op);
			var newid=$(op.contbox).find('input[name=newid]').val();
			if ($(op.contbox).find(':checkbox:checked').length>0) $('form#theForm input#form_update_data').val('Y');
			if (isNaN(newid)) return alert('Please input number.');
			$('form#theForm').find('input#newid').val(newid);
			$.post(CAT.op.move,$('form#theForm').serializeArray(),function(str){
				$('#insert_ids').remove();
				if ('Y'!=str) return Prompt.tips(str);
				Prompt.tips({width:200,content:Lang.update_success},function(){window.location.reload();});
			});
		};
		Prompt.confirm({content:'<p>Input new category id:</p><p><input type="text" name="newid" class="txt" /></p><p><input type="checkbox" id="update_data" value="Y" /> <label for="update_data">Also update data</label></p>',width:320,title:'Move to..'},fn);
	},
	resetForm:function(){
		$('form[name=theForm]')[0].reset();
	},
	add: function(){
		$('.addForm').show();
		var href=$(this).attr('href'),id=href.substr(href.lastIndexOf('#')+5);
		try{
			$('.editForm').hide().find('form')[0].reset();
			$('#addpid').val(id);
			$('#ele-textarea-adddataname')[0].focus();
			$('#ele-text-parentname').val($(this).parent().siblings('span:first').text());
		}catch(e){};
		return false;
	},
	edit: function(){
		$('.editForm').show();
		var href=$(this).attr('href'),id=href.substr(href.lastIndexOf('#')+6),
		data=jQuery.apptree.get_data($(this).attr('rel'),id);
		try{
			$('#editid').val(id);
			$('#ele-textarea-options').val(data.options);
			$('#ele-text-dataname').val(data.dataname/*$(this).parent().siblings('span').text()*/).get(0).select();
			$('.addForm').hide().find('form')[0].reset();
		}catch(e){};
		return false;
	},
	cancel: function(){
		$(this).closest('form')[0].reset();
		$('#addpid').val('0');
		if ($(this).closest('form[name=editForm]').length>0){
			$('.addForm,.editForm').toggle();
		}
	},
	del: function(){
		$.dialog('<h3>'+$.lang['confirm_delete']+'</h3><p>'+$.lang['caution_delete']+'</p>',
		{type:'questions', width:300, elem:this,
		buttons:{
			'submit':function(op){
				$(this).dialog('close');
				$.ajaxPost(jQuery.apptree.delaction,
				{id: $(op.elem).attr('rel'), tid:jQuery.apptree.tid, formhash:$.G.formhash},
				function(data){
					/*dialog.ok('<h3>'+(data.ok || data.message || 'Success!')+'</h3><p>'+($.lang['please_wait'] || '')+'</p>',
					{
						timeout:3,goto:'close',width:300,
						onclose: function(){
							var ob=$(op.elem).closest('ul').prev().find('>em'),num;
							if(ob.length>0){
								num=parseInt(ob.text(),10);
								ob.text('('+(num-1)+')');
							}
							$(op.elem).closest('li').remove();
						}
					});*/
					var ob=$(op.elem).closest('ul').prev().find('>em'),num;
					$(op.elem).closest('li').remove();
					if(ob.length>0){
						num=parseInt(ob.text().replace(/(\(|\))/ig, ''),10);
						ob.text('('+(num-1)+')');
						jQuery.apptree.tree(1==num ? ob.closest('ul') : ob.parent().next('ul'));
					}
				});
			},'cancel.cancel':function(){return true;}
		}});
		return false;
	},
	orderby: function(){
		var self=this, span=$(self).parent(), parent=$(self).closest('li'),
		ac=$(self).hasClass('arrow_up')?'up':'down', post={};
		if ($('.disabled',self).length>0) return false;
		var o=parseInt(span.attr('title'),10),
		po=parseInt(parent.prev().find('>span.arrow').attr('title'),10),
		no=parseInt(parent.next().find('>span.arrow').attr('title'),10);
		if ('up'==ac){
			parent.insertBefore(parent.prev());
		}else if('down'==ac){
			parent.insertAfter(parent.next());
		}
		jQuery.apptree.tree(parent.parent());
		if (o+po+no==0 || o+po==o || o+no==o){
			$('>li',parent.parent()).each(function(i){
				post[jQuery.apptree.get_id(this)] = i;
				$('>span.arrow',this).attr('title',i);
			});
		}else{
			if ('up'==ac){
				$('>span.arrow',parent.next()).attr('title',o);
				span.attr('title',po);
				post[jQuery.apptree.get_id(parent.next())] = o;
				post[jQuery.apptree.get_id(parent)] = po;
			}else if('down'==ac){
				$('>span.arrow',parent.prev()).attr('title',o);
				span.attr('title',no);
				post[jQuery.apptree.get_id(parent.prev())] = o;
				post[jQuery.apptree.get_id(parent)] = no;
			}
		};
		if (jQuery.isEmptyObject(post) || !jQuery.apptree.orderurl) return false;
		$.post(jQuery.apptree.orderurl, {updata:post, tid:jQuery.apptree.tid});
		return false;
	},
	save: function(json){
		if ($(this.form).attr('name')=='editForm'){
			var id=$('#editid').val(),
			arg={dataname:$('#ele-text-dataname').val()};
			$('>span:last','#cat_'+id).text(arg.dataname);
			jQuery.apptree.set_data(id, arg);
			dialog.ok('<p>'+json.ok+'</p>',{width:250,timeout:3,goto:'close'});
			return false;
		}
	},
	get_id: function(a){
		return $('div',a).attr('id').replace('cat_','');
	},
	get_data: function(pid,id){
		if('undefined'==typeof this.cache[pid]) return {};
		var k,row;
		for (k in this.cache[pid]){
			row = this.cache[pid][k];
			if (row.id==id) return row;
		}
		return {};
	},
	set_data: function(id,val){
		if('undefined'==typeof this.cache[id]) return ;
		var i,k,row;
		for (i in this.cache){
			for (k in this.cache[i]){
				row = this.cache[i][k];
				if (row.id==id)
					return jQuery.extend(this.cache[i][k], val||{});
			}
		}
	},
	init: function(a){
		jQuery.extend(this, a);
		//$('form[name=theForm]').saveForm();
		$('button.move').bind('click', this.move);
		$('.add').unbind('click').live('click', this.add);
		$('.edit').unbind('click').live('click', this.edit);
		$('.delete').unbind('click').live('click', this.del);
		$('button.cancel').bind('click', this.cancel);
		$('a', '.arrow').live('click', this.orderby);
		$('[name=addForm],[name=editForm]')
		.each(function(){
			$(this).saveForm(jQuery.apptree.save);
		});
		this.get(0);
	}
};
