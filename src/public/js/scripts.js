function toggleMergedBranches(_object) {
    if (_object.checked == true) {
        document.getElementById('merged_branch_block').style.display = 'block';
    }
    else {
        document.getElementById('merged_branch_block').style.display = 'none';
    }
}


function switchToBranch()
{
    //add a random number in url in order to ignore the cache
    var numRand = Math.floor(Math.random() * 101)
    window.location = 'index.php?branch=' + document.getElementById('select-active-branches').value + '&rand=' + numRand;
}


$(document).ready(function() {
    // submit the Hooks form
    var hooks_form = $('#apply_hooks_form');
    if (hooks_form) {
        hooks_form.ajaxForm({
            beforeSubmit: function() {
                $('#hook_list').html('Running hooks. Please wait...');
            },
            success: function(responseText) {
                $('#hook_list').html(responseText);
            }
        });
    }

    // submit the DB versioning form
    var db_scripts_form = $('#run_db_scripts_form');
    if (db_scripts_form) {
        db_scripts_form.ajaxForm({
            beforeSubmit: function() {
                $('#db_scripts_list').html('Running database scripts. Please wait...');
            },
            success: function(responseText) {
                $('#db_scripts_list').html(responseText);
            }
        });
    }

    var button_inputs = $('input[type=button]');
    if (button_inputs) {
        button_inputs.click(function(event) {
            button_inputs.each(function() {
                $(this).attr('disabled', 'disabled');
            });
        });
    }
});