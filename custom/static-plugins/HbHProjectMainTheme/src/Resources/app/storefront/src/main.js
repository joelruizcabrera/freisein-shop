/* Override Import here */
import ZoomModalPluginExtension from './script/override/zoom-modal-extension';

/* Custom Created Import here */
// import FindPluginName from './script/find-plugin-name';

const Manager = window.PluginManager;

/* Overrides here */
Manager.override('ZoomModal', ZoomModalPluginExtension, '[data-zoom-modal]');

/* Custom created here */
// Manager.register('FindPlugin', FindPluginName, 'html');
