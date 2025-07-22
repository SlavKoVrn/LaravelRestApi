<script>
$(function(){
    if (localStorage.getItem('sidebar-collapse') === 'true') {
        $('body').addClass('sidebar-collapse');
    }
    $('a[data-widget="pushmenu"]').click(function(){
        if ($('body').hasClass('sidebar-collapse')) {
            localStorage.setItem('sidebar-collapse', false);
        } else {
            localStorage.setItem('sidebar-collapse', true);
        }
    });
})
</script>
