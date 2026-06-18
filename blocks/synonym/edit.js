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
        register,
        tag,
        id,
        hstart,
        order,
        sort,
        lang,
        additional_class,
        color,
        load_open,
        expand_all_link,
        hide_title,
        hide_accordion,
        synonymstyle,
        synonym
    } = attributes;
    const blockProps = useBlockProps();
    const [registerstate, setSelectedCategories] = useState(['']);
    const [tagstate, setSelectedTags] = useState(['']);
    const [idstate, setSelectedIDs] = useState(['']);

    const categories = useSelect((select) => {
        return select('core').getEntityRecords('taxonomy', 'synonym_category');
    }, []);

    const registeroptions = [
        {
            label: __('all', 'wp-ai'),
            value: ''
        }
    ];

    if (!!categories) {
        Object.values(categories).forEach(register => {
            registeroptions.push({
                label: register.name,
                value: register.slug,
            });
        });
    }

    const tags = useSelect((select) => {
        return select('core').getEntityRecords('taxonomy', 'synonym_tag');
    }, []);

    const tagoptions = [
        {
            label: __('all', 'wp-ai'),
            value: ''
        }
    ];

    if (!!tags) {
        Object.values(tags).forEach(tag => {
            tagoptions.push({
                label: tag.name,
                value: tag.slug,
            });
        });
    }

    const synonyms = useSelect((select) => {
        return select('core').getEntityRecords('postType', 'bk_synonym', {per_page: -1, orderby: 'title', order: "asc"});
    }, []);

    const synonymoptions = [
        {
            label: __('all', 'wp-ai'),
            value: 0
        }
    ];

    if (!!synonyms) {
        Object.values(synonyms).forEach(synonym => {
            synonymoptions.push({
                label: synonym.title.rendered ? synonym.title.rendered : __('No title', 'wp-ai'),
                value: synonym.id,
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


    const synonymstyleoptions = [
        {
            label: __('-- hidden --', 'wp-ai'),
            value: ''
        },
        {
            label: __('A - Z', 'wp-ai'),
            value: 'a-z'
        },
        {
            label: __('Tagcloud', 'wp-ai'),
            value: 'tagcloud'
        },
        {
            label: __('Tabs', 'wp-ai'),
            value: 'tabs'
        }
    ];

    const coloroptions = [
        {
            label: 'fau',
            value: 'fau'
        },
        {
            label: 'med',
            value: 'med'
        },
        {
            label: 'nat',
            value: 'nat'
        },
        {
            label: 'phil',
            value: 'phil'
        },
        {
            label: 'rw',
            value: 'rw'
        },
        {
            label: 'tf',
            value: 'tf'
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
                            "Synonym",
                            'wp-ai'
                        )}
                        help={__('Show a selection of individual synonyms', 'wp-ai')}
                        value={idstate}
                        options={synonymoptions}
                        onChange={onChangeID}
                        multiple
                    />
                    <SelectControl
                        label={__(
                            "Language",
                            'wp-ai'
                        )}
                        help={__('Show only synonyms matching the selected language.', 'wp-ai')}
                        value={lang}
                        options={langoptions}
                        onChange={(value) => setAttributes({lang: value})}
                    />
                </PanelBody>
            </InspectorControls>
            <div {...blockProps}>
                <ServerSideRender
                    block="wp-ai/synonym"
                    attributes={attributes}
                />
            </div>
        </>
    );
}