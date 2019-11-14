
/*
 * Media block
 * -----------
 */

/* Dependencies */

const { 
    Panel,
    PanelBody,
    BaseControl,
    CheckboxControl,
    TextControl,
    Button
} = wp.components;

const { 
    MediaUpload,
    MediaUploadCheck,
    InspectorControls
} = wp.blockEditor;

const { Fragment } = wp.element;
const { registerBlockType } = wp.blocks;

/* Attributes from serverside */

const namespace = window.namespace;
const blockName = namespace + '/media';

const attr = window[namespace].blocks[blockName]['attr'];
const def = window[namespace].blocks[blockName]['default'];
const parent = window[namespace].blocks[blockName]['parent'];

/* Block */

let args = {
    title: 'Media',
    category: 'common',
    attributes: attr,
    edit: props => {
        const { attributes, setAttributes } = props;
        
        const { 
            id = def.id,
            url = def.url, 
            type = def.type,
            subtype = def.subtype,
            alt = def.alt,
            height = def.height,
            width = def.width,
            autoplay = def.autoplay,
            loop = def.loop,
            muted = def.muted,
            controls = def.controls,
            poster = def.poster,
            src = def.src
        } = attributes;

        /* Button callbacks */

        const setMedia = ( media ) => {
            let t = media.mime.split('/')[0],
                st = media.mime.split('/')[1],
                attr = { 
                    id: media.id,
                    type: t,
                    subtype: st,
                    width: media.width,
                    height: media.height
                };

            if( t == 'video' ) {
                let sources = { ...props.attributes.src }
                    sources[st] = media.url;

                attr['src'] = sources;
            }

            if( t == 'image' ) {
                attr['alt'] = media.alt;
                attr['url'] = media.url;
            }

            setAttributes( attr );
        };

        const setSrc = ( prop = '', val = '' ) => {
            const opts = { ...props.attributes.src };
            opts[prop] = val;

            props.setAttributes( { src: opts } );
        };

        const removeMedia = () => {
            setAttributes( {  id: '', type: '', url: '', src: def.src } );
        };

        /* Video */

        let videoSrc = '',
            videoSubtype = '',
            video = '';

        for( let s in src ) {
            if( src[s] !== '' ) {
                videoSubtype = s;
                videoSrc = src[s];
                break;
            }
        }

        if( id && videoSrc && videoSubtype ) {
            video = 
                `<video poster="${ poster ? poster : '' }">
                    <source src="${ videoSrc }" type="video/${ videoSubtype }">
                </video>`;   
        }

        let videoOutput = (
            <div>
                {
                    type != 'video' ? '' :
                    <Fragment>
                        <InspectorControls>
                            <PanelBody title={ 'Video Options' }>
                                <CheckboxControl
                                    label="Autoplay"
                                    value="1"
                                    checked={ autoplay ? true : false }
                                    onChange={ ( autoplay ) => { setAttributes( { autoplay } ) } }
                                />
                                <CheckboxControl
                                    label="Loop"
                                    value="1"
                                    checked={ loop ? true : false }
                                    onChange={ ( loop ) => { setAttributes( { loop } ) } }
                                />
                                <CheckboxControl
                                    label="Muted"
                                    value="1"
                                    checked={ muted ? true : false }
                                    onChange={ ( muted ) => { setAttributes( { muted } ) } }
                                />
                                <CheckboxControl
                                    label="Controls"
                                    value="1"
                                    checked={ controls ? true : false }
                                    onChange={ ( controls ) => { setAttributes( { controls } ) } }
                                />
                                <div>
                                    <MediaUploadCheck>
                                        { ['webm', 'mp4', 'ogv'].map( ( format, i ) => {
                                            return (
                                                <BaseControl label={ format }>
                                                     {
                                                        !src.hasOwnProperty( format ) || src[format] == ''
                                                        ?
                                                        <MediaUpload
                                                            onSelect={ setMedia }
                                                            allowedTypes={ [`video/${ format }`] }
                                                            render={ ( { open } ) => (
                                                                <Button isDefault onClick={ open }>Upload { format }</Button>
                                                            ) }
                                                        />
                                                        :
                                                        <div>
                                                            <Button isLink isDestructive onClick={ () => {
                                                                setSrc( format, '' );
                                                            } }>Remove { format }</Button>
                                                        </div>
                                                    }
                                                </BaseControl>
                                            );
                                        } ) }
                                    </MediaUploadCheck>
                                </div>
                            </PanelBody>
                        </InspectorControls>
                    </Fragment>
                }
                {
                    video 
                    ?
                    <div className="u-position-relative">
                        <Button className="o-remove-button" onClick={ removeMedia }>
                            <div>
                                <span className="u-visually-hidden">Remove Video</span>
                                <div className="o-remove-button__icon">&times;</div>
                            </div>
                        </Button>
                        <div className="o-media-wrap" dangerouslySetInnerHTML={ { __html: video } } />
                    </div>
                    : 
                    <div className="o-button-wrap">
                        <MediaUploadCheck>
                            <MediaUpload
                                onSelect={ setMedia }
                                allowedTypes={ ['video/webm', 'video/mp4', 'video/ogv'] }
                                render={ ( { open } ) => (
                                    <Button isDefault onClick={ open }>Upload Video</Button>
                                ) }
                            />
                        </MediaUploadCheck>
                    </div>
                }
            </div>
        );

        /* Image */

        let img = '';

        if( id && url )
            img = (
                <div className="o-media-wrap">
                    <img src={ url } alt={ alt } />
                </div>
            );

        let imgOutput = (
            <div className="u-position-relative">
                {
                    img 
                    ?
                    <div>
                        <Button className="o-remove-button" onClick={ removeMedia }>
                            <div>
                                <span className="u-visually-hidden">Remove Video</span>
                                <div className="o-remove-button__icon">&times;</div>
                            </div>
                        </Button>
                        { img }
                    </div>
                    : 
                    <div className="o-button-wrap">
                        <MediaUploadCheck>
                            <MediaUpload
                                onSelect={ setMedia }
                                allowedTypes={ ['image'] }
                                render={ ( { open } ) => (
                                    <Button isDefault onClick={ open }>Upload Image</Button>
                                ) }
                            />
                        </MediaUploadCheck>
                    </div>
                }
            </div>
        );

        // make sure no type or right type
        videoOutput = type != 'image' ? videoOutput : '';
        imgOutput = type != 'video' ? imgOutput : '';

        return [
            videoOutput,
            imgOutput
        ];
    },
    save() {
        return null;
    }
};

if( parent.length > 0 )
    args['parent'] = parent;

registerBlockType( blockName, args );
