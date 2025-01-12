(
    function ($) {
        class UFAE_Review_Form {
            constructor() {
                this.init();
            }

            init() {
                const $notice = $('.ufae-review-notice .ufae-review-notice-buttons button');
                $notice.on('click', this.ufae_review_dismiss);
            }

            ufae_review_dismiss(e) {
                const nonce = ufae_review_obj.nonce;
                const noticeWrp = jQuery(this).closest('.ufae-review-notice');
                jQuery.ajax({
                    url: ufae_review_obj.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'ufae_review_dismiss',
                        ufae_review_dismiss: true,
                        nonce: nonce,
                    },
                    success: function (response) {
                        noticeWrp.fadeOut(500);
                    },
                })
            }
        }

        new UFAE_Review_Form();
    }
)(jQuery)