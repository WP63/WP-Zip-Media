(($) => {
  if( $('body').hasClass('upload-php') ) {
    $('.bulkactions select').on('change', (e) => {
      let $this = $(e.currentTarget);

      if( $this.val() === 'download' ) {
        $this.after('<input type="text" class="download-filename" name="bundle_file_name" class="" placeholder="Enter file name ..." required>');
      } else {
        $('.download-filename').remove();
      }
    });
  }
})(jQuery)
