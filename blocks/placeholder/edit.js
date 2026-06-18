/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-i18n/
 */
import {__} from '@wordpress/i18n';
import {useState} from '@wordpress/element';
import {useSelect} from '@wordpress/data';
import {InspectorControls, useBlockProps} from '@wordpress/block-editor';
import {PanelBody, SelectControl} from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';


export default function Edit({attributes, setAttributes}) {
    const {
        id,
        order,
        sort,
        lang,
        additional_class
    } = attributes;
    const blockProps = useBlockProps();
    const [idstate, setSelectedIDs] = useState(['']);

    // const categories = useSelect((select) => {
    //     return select('core').getEntityRecords('taxonomy', 'synonym_category');
    // }, []);

    // const registeroptions = [
    //     {
    //         label: __('all', 'wp-ai'),
    //         value: ''
    //     }
    // ];

    // if (!!categories) {
    //     Object.values(categories).forEach(register => {
    //         registeroptions.push({
    //             label: register.name,
    //             value: register.slug,
    //         });
    //     });
    // }

    // const tags = useSelect((select) => {
    //     return select('core').getEntityRecords('taxonomy', 'synonym_tag');
    // }, []);

    // const tagoptions = [
    //     {
    //         label: __('all', 'wp-ai'),
    //         value: ''
    //     }
    // ];

    // if (!!tags) {
    //     Object.values(tags).forEach(tag => {
    //         tagoptions.push({
    //             label: tag.name,
    //             value: tag.slug,
    //         });
    //     });
    // }

    const placeholders = useSelect((select) => {
        return select('core').getEntityRecords('postType', 'bk_placeholder', {per_page: -1, orderby: 'title', order: "asc"});
    }, []);

    const placeholderoptions = [
        {
            label: __('all', 'wp-ai'),
            value: 0
        }
    ];

    if (!!placeholders) {
        Object.values(placeholders).forEach(placeholder => {
            placeholderoptions.push({
                label: placeholder.title.rendered ? placeholder.title.rendered : __('No title', 'wp-ai'),
                value: placeholder.id,
            });
        });
    }

    const langoptions = [
        {
            label: __('all', 'wp-ai'),
            value: ''
        },
        {
            label: __('German', 'wp-ai'),
            value: 'de'
        },
        {

            label: __('English', 'wp-ai'),
            value: 'en'
        },
        {

            label: __('French', 'wp-ai'),
            value: 'fr'
        },
        {

            label: __('Spanish', 'wp-ai'),
            value: 'es'
        },
        {
            label: __('Russian', 'wp-ai'),
            value: 'ru'
        },
        {
            label: __('Chinese', 'wp-ai'),
            value: 'zh'
        }
    ];

    const sortoptions = [
        {
            label: __('Title', 'wp-ai'),
            value: 'title'
        },
        {
            label: __('ID', 'wp-ai'),
            value: 'id'
        },
        {
            label: __('Sort field', 'wp-ai'),
            value: 'sortfield'
        }
    ];

    const orderoptions = [
        {
            label: __('ASC', 'wp-ai'),
            value: 'ASC'
        },
        {
            label: __('DESC', 'wp-ai'),
            value: 'DESC'
        }
    ];

    // console.log('edit.js attributes: ' + JSON.stringify(attributes));

    const onChangeID = (newValues) => {
        setSelectedIDs(newValues);
        setAttributes({id: String(newValues)})
        };

    return (
        <>
            <InspectorControls>
                <PanelBody>
                    <SelectControl
                        label={__(
                            "Placeholder",
                            'wp-ai'
                        )}
                        help={__('Show a selection of individual placeholders', 'wp-ai')}
                        value={idstate}
                        options={placeholderoptions}
                        onChange={onChangeID}
                        multiple
                    />
                    <SelectControl
                        label={__(
                            "Language",
                            'wp-ai'
                        )}
                        help={__('Show only placeholders matching the selected language.', 'wp-ai')}
                        value={lang}
                        options={langoptions}
                        onChange={(value) => setAttributes({lang: value})}
                    />
                </PanelBody>
            </InspectorControls>
            <div {...blockProps}>
                <ServerSideRender
                    block="wp-ai/placeholder"
                    attributes={attributes}
                />
            </div>
        </>
    );
}