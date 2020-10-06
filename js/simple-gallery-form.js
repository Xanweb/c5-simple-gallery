/* jshint unused:vars, undef:true, browser:true, jquery:true */

class SimpleGalleryManagerSelect {
    /**
     * Construct SimpleGalleryManagerSelect.
     *
     * @param  {Object} options
     */
    constructor(options = {}) {
        const my = this
        var thumbnailTemplate = _.template($('#thumbnailTemplate').html());
        // Setup Images Sort
        $("#ccm-gallery-dnd-sortable").sortable();
        my._setupFileManagerButton(thumbnailTemplate, options);
        my._setupDeleteImageAction();
        my._setupEditPropertiesButton();
    }

    _setupFileManagerButton(thumbnailTemplate, options) {
        const my = this;
        $('#file_manager_button').click(function () {
            ConcreteFileManager.launchDialog(function (data) {
                my._loadFileDetails(data.fID, thumbnailTemplate);
            }, {
                multipleSelection: true,
                i18n: options
            });
        });

    }
    ;
            _loadFileDetails(fID, thumbnailTemplate) {
        var $container = $("#ccm-gallery-dnd-sortable");
        var preloader = '<div class="col-sm-3">';
        preloader += '<div class="pre-loader"><i class="fa fa-spinner fa-spin" aria-hidden="true"></i></div>';
        preloader += '</div>';
        $container.append(preloader);
        var $newLine = $container.children().last();

        $.ajax({
            type: 'post',
            dataType: 'json',
            url: CCM_DISPATCHER_FILENAME + '/ccm/simple/gallery/file/details/get',
            data: {'fID': fID},
            error: function (r) {
                ConcreteAlert.dialog(ccmi18n.error, r.responseText);
            },
            success: function (files) {
                var file = files[0];

                jQuery.fn.dialog.hideLoader();
                if (!isNaN(file.fID)) {

                    var fileParam = {fID: file.fID, thumbnail: file.resultsThumbnailImg};
                    $newLine.replaceWith(thumbnailTemplate({file: fileParam}));
                    $newLine = $container.find('.ccm-gallery-dnd-im-container').eq($newLine.index());
                    $newLine.find('img').removeClass('ccm-file-manager-list-thumbnail')
                            .addClass('ccm-gallery-dnd-im img-rounded');

                    if ($newLine.find('.fTitle').val().trim() == "") {
                        $newLine.find('.fTitle').val(file.ftitle);
                    }

                    if ($newLine.find('.fCaption').val().trim() == "") {
                        $newLine.find('.fCaption').val(file.caption);
                    }

                    if ($newLine.find('.fCopyright').val().trim() == "") {
                        $newLine.find('.fCopyright').val(file.copyright);
                    }

                    if ($newLine.find('.fAltText').val().trim() == "") {
                        $newLine.find('.fAltText').val(file.alt);
                    }

                    $container.sortable("refresh");
                }
            }
        });
    }
    ;
            _setupDeleteImageAction() {
        $('#file-location-upl').on('click', '.ccm-gallery-dnd-im-delete', function (e) {
            e.preventDefault();
            $(this).closest('.ccm-gallery-dnd-im-container').fadeOut(500, function () {
                $(this).remove();
            });
        });
    }
    ;
            _setupEditPropertiesButton() {
        $('#file-location-upl').on('click', '.ccm-gallery-dnd-im-edit', function (e) {
            e.preventDefault();
            var $container = $(this).closest('.ccm-gallery-dnd-im-container');
            $.fn.dialog.open({
                title: $(this).attr('title'),
                href: CCM_DISPATCHER_FILENAME + '/ccm/simple/gallery/file/details?fID=' + $(this).attr('data-fid'),
                data: {
                    title: $container.find('.fTitle').val().replace(/\\"/g, '"'),
                    caption: $container.find('.fCaption').val().replace(/\\"/g, '"'),
                    altText: $container.find('.fAltText').val().replace(/\\"/g, '"'),
                    copyright: $container.find('.fCopyright').val().replace(/\\"/g, '"'),
                    showCopyright: $container.find('.fShowCopyright').val()
                },
                width: 680,
                height: 450,
                modal: true,
                onOpen: function ($dialog) {
                    $dialog.parent().find('.btn-success').click(function (e) {
                        e.preventDefault();
                        $container.find('.fTitle').val($dialog.find('#title').val().replace(/"/g, '\\"'));
                        $container.find('.fCaption').val($dialog.find('#caption').val().replace(/"/g, '\\"'));
                        $container.find('.fAltText').val($dialog.find('#altText').val().replace(/"/g, '\\"'));
                        $container.find('.fCopyright').val($dialog.find('#ccm-gallery-copyright').val().replace(/"/g, '\\"'));
                        $container.find('.fShowCopyright').val($dialog.find('#showCopyright').is(':checked') ? 1 : 0);
                        jQuery.fn.dialog.closeTop();
                    });
                }
            });
        });
    }
    ;
}

$(function () {
    let options = $('.simple-gallery-form').data('options');
    new SimpleGalleryManagerSelect(options);

    Concrete.event.bind('open.block.simple-gallery', function (e, data) {

        var uniqueID = data.uniqueID;
        var formContainer = $('#form-container-' + uniqueID);

        formContainer.on('change', '.js-file-location', function (e2) {
            e2.preventDefault();
            var fileLocation = $(this).val();
            formContainer.find('.file-locations').hide();
            formContainer.find('#file-location-' + $(this).val()).show();
        });

        formContainer.on('change', '.js-lightbox-caption', function (e2) {
            e2.preventDefault();
            var lightboxCaption = $(this).val();
            if (lightboxCaption == 'common') {
                formContainer.find('.js-common-caption-wrapper').show();
            } else {
                formContainer.find('.js-common-caption-wrapper').hide();
            }
        });

        formContainer.on('change', '.js-fileset-id', function (e2) {

            e2.preventDefault();

            var filesetID = parseInt($(this).val());

            if (filesetID) {
                var filesetDetail = formContainer.find('.js-fileset-detail-url').val();
                formContainer.find('.js-text-fileset-selected a').attr('href', filesetDetail + '/' + filesetID);
                formContainer.find('.js-text-fileset-selected').show();
                formContainer.find('.js-text-fileset-not-selected').hide();
            } else {
                formContainer.find('.js-text-fileset-selected').hide();
                formContainer.find('.js-text-fileset-not-selected').show();
            }
        });
    });
})

