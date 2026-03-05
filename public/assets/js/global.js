$(document).ready(function() {

    /*X-CSRF-TOKEN*/
    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    $('input[id="name"]').focus();

    $(document).on('click','[data-plugin="delete-data"]',function(e) {
		e.preventDefault();
        var targetForm = $(this).data('target-form');
        if (!confirm('Are you sure you want to delete?')) return;
        e.preventDefault();
        $(targetForm).submit();
	});

    $(document).on('click','[data-plugin="submit-form"]',function(e) {
		e.preventDefault();
        var targetForm = $(this).data('target-form');
        var confMessage = $(this).data('conf');
        if (!confirm(confMessage)) return;
        e.preventDefault();
        $(targetForm).submit();
	});

    // $(document).on('click','.btn-close',function(e) {
    //     e.preventDefault();
    //     if (!confirm('Are you sure you want to delete?')) return;
    //     var $this = $(this);
    //     var item_container = $(this).data('target');
    //     $(item_container).remove();
    // });

    $(document).on('click','[data-plugin="confirm-data"]',function(e) {
		e.preventDefault();
        var targetUrl = $(this).attr('href');
        var confirmText = $(this).data('confirmtext');
        // console.log(confirmText);
        // SweetAlert2 confirmation dialog
        Swal.fire({
            title: 'Are you sure?',
            text: confirmText,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                goBlankLink(targetUrl)
                // Action to take if confirmed (e.g., delete or submit)
                // Swal.fire(
                //     'Deleted!',
                //     'Your item has been deleted.',
                //     'success'
                // );
            } else if (result.dismiss === Swal.DismissReason.cancel) {
                // Action to take if canceled
                // Swal.fire(
                //     'Cancelled',
                //     'Your item is safe.',
                //     'info'
                // );
            }
        });


        // if (!confirm('Are you sure you want to delete?')) return;
        // e.preventDefault();
        // $(targetForm).submit();
	});

    table_min_height();

});


function goLink(url) {
    window.location.href = url
}

function goBlankLink(url) {
    // window.location.href = url
    window.open(url, '_blank');
}

function refreshPage() {
    location.reload();
}

function goBack() {
    window.history.back();
}

function table_min_height() {
    $("div.table-responsive").addClass("table_min_height");
}

