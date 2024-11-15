import ZoomModalPlugin from '../../../../../../../../../../vendor/shopware/storefront/Resources/app/storefront/src/plugin/zoom-modal/zoom-modal.plugin.js';
import PluginManager from 'src/plugin-system/plugin.manager';

export default class ZoomModalPluginExtension extends ZoomModalPlugin {
    init() {
        super.init();
    }

    _getParentSliderIndex() {
        let sliderIndex = 1;

        this._parentSliderElement = this.el.closest(this.options.parentGallerySliderSelector);

        if (this._parentSliderElement) {
            this._parentSliderPlugin = PluginManager.getPluginInstanceFromElement(this._parentSliderElement, 'GallerySlider');

            if (this._parentSliderPlugin) {
                sliderIndex = this._parentSliderPlugin.getCurrentSliderIndex();
            }
        }

        /* removed +1 slider index */
        return sliderIndex;
    }
}
