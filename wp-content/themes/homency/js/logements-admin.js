jQuery(document).ready(function($) {
    var frame;
    $('.upload_gallery_button').on('click', function(e) {
        e.preventDefault();

        if (frame) {
            frame.open();
            return;
        }

        frame = wp.media({
            title: 'Select or Upload Images',
            button: {
                text: 'Use this media'
            },
            multiple: true
        });

        frame.on('select', function() {
            var attachments = frame.state().get('selection').toJSON();
            var gallery = $('#logements_gallery');
            var galleryInput = $('#logements_gallery_input');
            var galleryIds = galleryInput.val() ? galleryInput.val().split(',') : [];

            attachments.forEach(function(attachment) {
                if (attachment.sizes && attachment.sizes.thumbnail) {
                    var imageUrl = attachment.sizes.thumbnail.url;
                    var imageId = attachment.id;
                    gallery.append('<li class="image" data-id="' + imageId + '"><img src="' + imageUrl + '" /><a href="#" class="remove_image">Remove</a></li>');
                    galleryIds.push(imageId);
                } else {
                    console.log('Thumbnail size not found for attachment ID ' + attachment.id);
                }
            });

            galleryInput.val(galleryIds.join(','));
        });

        frame.open();
    });

    $('#logements_gallery').on('click', '.remove_image', function(e) {
        e.preventDefault();
        var image = $(this).closest('li');
        var imageId = image.data('id');
        var galleryInput = $('#logements_gallery_input');
        var galleryIds = galleryInput.val().split(',');

        galleryIds = galleryIds.filter(function(id) {
            return id != imageId;
        });

        galleryInput.val(galleryIds.join(','));
        image.remove();
    });

    $('#logements_gallery').sortable({
        update: function(event, ui) {
            var galleryInput = $('#logements_gallery_input');
            var galleryIds = [];

            $('#logements_gallery .image').each(function() {
                var id = $(this).data('id');
                galleryIds.push(id);
            });

            galleryInput.val(galleryIds.join(','));
        }
    });
});
