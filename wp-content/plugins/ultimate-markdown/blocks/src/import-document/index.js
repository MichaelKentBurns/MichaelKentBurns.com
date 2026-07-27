const {registerPlugin} = wp.plugins;
import render from './components/Sidebar';

registerPlugin(
    'daextulma-import-document',
    {
      icon: false,
      render,
    },
);