(function ($) {

    class UFAE_Feedback_Form {
        constructor() {
            this.data = ufaeFeedbackData;
            this.mainWrapper = jQuery('.ufae-deactivate-feedback-form-wrapper[data-slug="' + this.data.pluing_slug + '"]');
            this.deactivateButton = jQuery('#the-list tr[data-slug="' + this.data.pluing_slug + '"] span.deactivate a');
            this.deactivateLink = this.deactivateButton.attr('href');
            this.init();
        }

        init() {
            this.deactivateButtonHandler();
            this.closeButtonHandler();
            this.submitButtonHandler();
            this.skipButtonHandler();
            this.confirmButtonHandler();
        }

        deactivateButtonHandler() {
            if (this.deactivateButton.length > 0) {
                this.deactivateButton.on('click', this.showFeedbackForm.bind(this));
            }
        }

        closeButtonHandler() {
            this.mainWrapper.find('.ufae-deactivate-close').on('click', () => {
                this.mainWrapper.addClass('ufae-form-hide');
            });
        }

        showFeedbackForm(event) {
            event.preventDefault();
            this.mainWrapper.removeClass('ufae-form-hide');
        }

        submitButtonHandler() {
            var submitButton = this.mainWrapper.find('.ufae-button-wrapper .button-secondary');
            if (submitButton) {
                submitButton.on('click', this.submitFeedbackForm);
            }
        }

        skipButtonHandler() {
            var submitButton = this.mainWrapper.find('.ufae-button-wrapper .button-primary');
            if (submitButton) {
                submitButton.on('click', (e) => {
                    e.preventDefault();
                    window.location = this.deactivateLink;
                });
            }
        }

        confirmButtonHandler() {
            var confirmButton = this.mainWrapper.find('input[name="confirm"]');
            if (confirmButton) {
                confirmButton.on('click', (e)=>{
                    this.mainWrapper.find('.ufae-button-wrapper .button-secondary').toggleClass('confirmed',e.target.checked);
                });
            }
        }

        submitFeedbackForm = (event) => {
            event.preventDefault();

            var reason = this.mainWrapper.find('input[name="reason"]:checked').val();
            var message = this.mainWrapper.find('input[name="reason"]:checked').next().next().val();
            var confirm = this.mainWrapper.find('input[name="confirm"]:checked');
            var nonce = this.mainWrapper.find('input[name="ufae_send_feedback_nonce"]').val();

            if (reason && confirm.length > 0) {
                var data = {
                    'action': 'ufae_send_feedback',
                    'reason': reason,
                    'message': message,
                    'nonce': nonce
                };
                this.sendFeedback(data);
            }
        }

        sendFeedback(data) {
            jQuery.ajax({
                type: 'POST',
                url: ufaeFeedbackData.ajax_url,
                data: data,
                success:  (response)=> {
                    if (response.success) {
                        window.location = this.deactivateLink;
                    }
                }
            });
        }
    }

    new UFAE_Feedback_Form();

})(jQuery);


