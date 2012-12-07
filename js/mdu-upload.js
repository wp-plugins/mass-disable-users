jQuery(document).ready( function() {

  // Select all checkbox on confirmation page
  jQuery('#selectAll').click( function() {
    var checkedStatus = this.checked;
    jQuery('#email-list tbody tr').find('td:first :checkbox').each( function() {
      jQuery(this).prop('checked', checkedStatus);
    });
  });

});
