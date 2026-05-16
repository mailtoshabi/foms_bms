<!-- JAVASCRIPT -->
<script src="{{ URL::asset('/assets/libs/jquery/jquery.min.js') }}"></script>
<script src="{{ URL::asset('/assets/libs/bootstrap/bootstrap.min.js') }}"></script>
<script src="{{ URL::asset('/assets/libs/metismenu/metismenu.min.js') }}"></script>
<script src="{{ URL::asset('/assets/libs/simplebar/simplebar.min.js') }}"></script>
<script src="{{ URL::asset('/assets/libs/node-waves/node-waves.min.js') }}"></script>
<script src="{{ URL::asset('/assets/libs/feather-icons/feather-icons.min.js') }}"></script>
<!-- pace js -->
<script src="{{ URL::asset('assets/libs/pace-js/pace-js.min.js') }}"></script>
<script src="{{ URL::asset('assets/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script src="{{ URL::asset('assets/js/global.js') }}?v={{ filemtime(public_path('assets/js/global.js')) }}"></script>
<script src="{{ URL::asset('assets/libs/select2/select2.min.js') }}"></script>
@yield('script')
@yield('script-bottom')
<script>
$(document).ready(function() {
    function initSelect2(context) {
        var parent = context || document;
        $(parent).find('.select2').not('[data-select2-id]').select2({ placeholder: 'Search...', allowClear: true, width: '100%' });
        $(parent).find('.select2-class-ajax').not('[data-select2-id]').each(function() {
            var $el = $(this);
            var ajaxUrl = $el.data('ajax-url');
            $el.select2({
                placeholder: 'Search class...', allowClear: true, width: '100%',
                minimumInputLength: 0,
                ajax: { url: ajaxUrl, dataType: 'json', delay: 250,
                    data: function(p) { return { q: p.term || '' }; },
                    processResults: function(d) { return { results: d.results }; },
                    cache: true
                }
            });
        });
    }
    initSelect2();
    $(document).on('show.bs.modal', '.modal', function() {
        var $modal = $(this);
        $modal.find('select.select2').each(function() {
            if ($(this).hasClass('select2-hidden-accessible')) { $(this).select2('destroy'); }
            $(this).select2({ placeholder: 'Search...', allowClear: true, width: '100%', dropdownParent: $modal });
        });
        $modal.find('select.select2-class-ajax').each(function() {
            var $el = $(this);
            if ($el.hasClass('select2-hidden-accessible')) { $el.select2('destroy'); }
            $el.select2({
                placeholder: 'Search class...', allowClear: true, width: '100%',
                minimumInputLength: 0, dropdownParent: $modal,
                ajax: { url: $el.data('ajax-url'), dataType: 'json', delay: 250,
                    data: function(p) { return { q: p.term || '' }; },
                    processResults: function(d) { return { results: d.results }; },
                    cache: true
                }
            });
        });
    });

    // Auto prepend https:// to google meet link
    $(document).on('blur', 'input[name="google_meet_link"]', function() {
        var val = $(this).val().trim();
        if (val && !/^https?:\/\//i.test(val)) {
            $(this).val('https://' + val);
        }
    });
});
</script>
<!-- PWA -->
{{-- <script>window.swUrl = '{{ asset('sw.js') }}';</script>
<script src="{{ URL::asset('assets/js/pwa.js') }}" defer></script> --}}
@include('pwa')
