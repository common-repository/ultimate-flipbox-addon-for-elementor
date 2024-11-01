
class UfaeCommon extends elementorModules.frontend.handlers.Base {
    getDefaultSettings() {
        return {
            selectors: {
                flipboxContainer: '.ufae-container',
            },
        };
    }

    getDefaultElements() {
        const selectors = this.getSettings('selectors');
        return {
            $flipboxContainer: this.$element.find(selectors.flipboxContainer),
        };
    }

    bindEvents() {
        this.editorAnimation();
        this.curtainAnimation();
    }

    editorAnimation() {
        const flipboxContainer = this.elements.$flipboxContainer;
        const editorActiveWidget = flipboxContainer.closest('.elementor-widget-ufae_flipbox_widget.elementor-element-editable')[0];
        if (editorActiveWidget && editorActiveWidget.dataset['ufaeActiveRepeater']) {
            const activeRepeaterId = editorActiveWidget.dataset.ufaeActiveRepeater;
            flipboxContainer.find(`.ufae-flipbox-item.elementor-repeater-item-${activeRepeaterId}`).addClass('ufae-repeater-active');
        }
    }

    curtainAnimation() {

        const flipboxContainer = this.elements.$flipboxContainer;
        const flipboxInnerWrps = flipboxContainer.find(".ufae-flipbox-inner");
        let transitionTime = flipboxContainer.data('ufae-transition') || 1000;
        transitionTime = parseInt(transitionTime);

        const animationEffect = (e, status) => {
            const frontDuplicateOverlay = e.find('.ufae-front-duplicate_overlay');

            if (status) {
                frontDuplicateOverlay.css('transition', 'opacity 80ms').css('opacity', 1);
            } else {
                frontDuplicateOverlay.css('transition', 'all 0s').css('opacity', 0);
            }
        }

        flipboxInnerWrps.each((_, ele) => {
            const innerWrp = jQuery(ele);
            let curtainTimeout = '';

            innerWrp.on('mouseenter', () => {
                animationEffect(innerWrp, false);
                clearTimeout(curtainTimeout); // Use a property to store the timeout ID
            }).on('mouseleave', () => {
                // const innerWrpWidth = innerWrp.width();
                // const frontDuplicateLeft = innerWrp.find('.ufae-front_duplicate').position().left || (innerWrpWidth / 2);
                // const currentTransition = ((transitionTime / (innerWrpWidth / 2)) * frontDuplicateLeft);
                curtainTimeout = setTimeout(() => { animationEffect(innerWrp, true) }, (transitionTime - 100)); // Store the timeout ID
            });
        })
    }
}


jQuery(window).on('elementor/frontend/init', () => {

    const addHandler = ($element) => {
        elementorFrontend.elementsHandler.addHandler(UfaeCommon, {
            $element,
        });
    };

    elementorFrontend.hooks.addAction('frontend/element_ready/ufae_flipbox_widget.default', addHandler);

});