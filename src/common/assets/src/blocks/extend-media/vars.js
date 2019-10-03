
/*
 * Variables
 * ---------
 */

const allowedBlocks = [
    'core/image', 
    'core/video', 
    'core-embed/youtube', 
    'core-embed/vimeo'
];

const widthOptions = [
    {
        label: 'None',
        value: '',
    },
    {
        label: '33%',
        value: '33',
    },
    {
        label: '50%',
        value: '50',
    },
    {
        label: '66%',
        value: '66',
    },
    {
        label: '100%',
        value: '100',
    },
    {
        label: 'Breakout',
        value: 'breakout'
    }
];

export { allowedBlocks, widthOptions };
