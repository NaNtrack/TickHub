jQuery(document).ready(function() {
	jQuery( "#tickspot_date" ).datepicker({ dateFormat: "mm-dd-yy" });
});
function updateProjects (client) {
	jQuery.ajax({
		type     : 'POST',
		cache    : false,
		url      : '/ajax/tickspot_cpt/',
		data	 : 's=projects&c='+client,
		dataType : 'json',
		success  : function(response){
			jQuery.fancybox.hideActivity();
			if ( response.result == 'OK' ) {
				jQuery.fancybox.close();
				var options = '<option value=""> - Select - </option>';
				for (var i = 0; i < response.projects.length; i++) {
					options += '<option value="' + response.projects[i].id + '">' + response.projects[i].name + '</option>';
				}
				$("#tickspot_project").html(options);
				$("#tickspot_task").html('<option value="">---</option>');
			} else {
				$("#tickspot_project").html('<option value="">---</option>');
				$("#tickspot_task").html('<option value="">---</option>');
				alert(response.reason);
			}
		},
		beforeSend : function () {
			jQuery.fancybox.showActivity();
		}, 
		error: function (jqXHR, textStatus, errorThrown) {
			jQuery.fancybox.hideActivity();
			var error = 'An error ocurred';
			if ( textStatus != null ) {
				error += ' ('+textStatus+')'
			}
			if ( errorThrown != null) {
				error += ': ' + errorThrown;
			}
			alert(error);
		}
	});
}

function updateTasks (project) {
	jQuery.ajax({
		type     : 'POST',
		cache    : false,
		url      : '/ajax/tickspot_cpt/',
		data	 : 's=tasks&p='+project,
		dataType : 'json',
		success  : function(response){
			jQuery.fancybox.hideActivity();
			if ( response.result == 'OK' ) {
				jQuery.fancybox.close();
				var options = '<option value=""> - Select - </option>';
				for (var i = 0; i < response.tasks.length; i++) {
					options += '<option value="' + response.tasks[i].id + '">' + response.tasks[i].name + '</option>';
				}
				$("#tickspot_task").html(options);
			} else {
				$("#tickspot_task").html('<option value="">---</option>');
				alert(response.reason);
			}
		},
		beforeSend : function () {
			jQuery.fancybox.showActivity();
		}, 
		error: function (jqXHR, textStatus, errorThrown) {
			jQuery.fancybox.hideActivity();
			var error = 'An error ocurred';
			if ( textStatus != null ) {
				error += ' ('+textStatus+')'
			}
			if ( errorThrown != null) {
				error += ': ' + errorThrown;
			}
			alert(error);
		}
	});
}


function updateEmails (repo) {
	jQuery.ajax({
		type     : 'POST',
		cache    : false,
		url      : '/ajax/github_repo_email/',
		data	 : 'r='+repo,
		dataType : 'json',
		success  : function(response){
			jQuery.fancybox.hideActivity();
			if ( response.result == 'OK' ) {
				jQuery.fancybox.close();
				var checkboxes = '';
				for (var i = 0; i < response.emails.length; i++) {
					checkboxes += '<input type="checkbox" name="github_emails[]" value="'+response.emails[i].author_email+'" /> <span>'+response.emails[i].author_name+'</span>';
				}
				$("#github_emails").html(checkboxes);
			} else {
				$("#github_emails").html('');
				alert(response.reason);
			}
		},
		beforeSend : function () {
			jQuery.fancybox.showActivity();
		}, 
		error: function (jqXHR, textStatus, errorThrown) {
			jQuery.fancybox.hideActivity();
			var error = 'An error ocurred';
			if ( textStatus != null ) {
				error += ' ('+textStatus+')'
			}
			if ( errorThrown != null) {
				error += ': ' + errorThrown;
			}
			alert(error);
		}
	});
}


function updateCommits () {
	var repo = jQuery('#github_repository').val();
	var emails = [];
	$('#github_emails :checked').each(function() {
		emails.push(jQuery(this).val());
	});
	
	jQuery.ajax({
		type     : 'POST',
		cache    : false,
		url      : '/ajax/github_commits/',
		data	 : 'r='+repo+'&e='+emails,
		dataType : 'json',
		success  : function(response){
			jQuery.fancybox.hideActivity();
			if ( response.result == 'OK' ) {
				jQuery.fancybox.close();
				var li = '';
				for (i = 0 ; i < response.commits.length ; i++ ) {
					li += '<li id="commit_'+response.commits[i].commit_id+'"><span class="message">' + response.commits[i].message + '</span><span class="commit_author"> by '+response.commits[i].author_name+' ('+response.commits[i].author_email+')</span><span class="commit_date">'+response.commits[i].date+'</span><a href="#" onclick="return hideCommit('+response.commits[i].commit_id+')">Hide</a><a href="#" onclick="return addCommit('+response.commits[i].commit_id+')">Add</a></li>';
				}
				li += '<li>&nbsp;</li>';
				jQuery('#github_commits').html(li);
			} else {
				jQuery('#github_commits').html('<li>&nbsp;</li>');
				alert(response.reason);
			}
		},
		beforeSend : function () {
			jQuery.fancybox.showActivity();
		}, 
		error: function (jqXHR, textStatus, errorThrown) {
			jQuery.fancybox.hideActivity();
			var error = 'An error ocurred';
			if ( textStatus != null ) {
				error += ' ('+textStatus+')'
			}
			if ( errorThrown != null) {
				error += ': ' + errorThrown;
			}
			alert(error);
		}
	});
	return false;
}


function addCommit (id) {
	var li = jQuery('#commit_'+id);
	var message = jQuery('#tickspot_message').val();
	var repo = jQuery("#github_repository option[value='"+jQuery('#github_repository').val()+"']").text();
	jQuery.ajax({
		type     : 'POST',
		cache    : false,
		url      : '/ajax/add_commit/',
		data	 : 'id='+id,
		dataType : 'json',
		beforeSend : function () {
			jQuery('#tickspot_message').val(message+'- '+li.find('span.message').html()+'\n');
			li.fadeOut(100,function(){
				jQuery(this).remove();
			});
		}
	});
	return false;
}

function hideCommit (id) {
	jQuery.ajax({
		type     : 'POST',
		cache    : false,
		url      : '/ajax/hide_commit/',
		data	 : 'id='+id,
		dataType : 'json',
		beforeSend : function () {
			jQuery('#commit_'+id).fadeOut(100,function(){
				jQuery(this).remove();
			});
		}
	});
	return false;
}

function createTickspotEntry () {
	jQuery.ajax({
		type     : 'POST',
		cache    : false,
		url      : '/ajax/tickspot_entry/',
		data	 : jQuery('#form_tickspot_entry').serialize(),
		dataType : 'json',
		success  : function(response){
			jQuery.fancybox.hideActivity();
			if ( response.result == 'OK' ) {
				jQuery('#tickspot_message').val('');
				alert('Entry created!');
			} else {
				alert(response.reason);
			}
		},
		beforeSend : function () {
			jQuery.fancybox.showActivity();
		}, 
		error: function (jqXHR, textStatus, errorThrown) {
			jQuery.fancybox.hideActivity();
			var error = 'An error ocurred';
			if ( textStatus != null ) {
				error += ' ('+textStatus+')'
			}
			if ( errorThrown != null) {
				error += ': ' + errorThrown;
			}
			alert(error);
		}
	});
	return false;
}