/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-i18n/
 */
import { __ } from '@wordpress/i18n';
import { useEffect, useState } from '@wordpress/element';
import { useSelect } from '@wordpress/data';
import {
	InspectorControls,
	useBlockProps,
	HeadingLevelDropdown,
	BlockControls,
} from '@wordpress/block-editor';
import {
	PanelBody,
	ToggleControl,
	SelectControl,
	RangeControl,
} from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';


function buildCategoryOptions(categories) {
	const map = new Map();
	const roots = [];

	categories.forEach((cat) => {
		cat.children = [];
		map.set(cat.id, cat);
	});

	categories.forEach((cat) => {
		if (cat.parent && map.has(cat.parent)) {
			map.get(cat.parent).children.push(cat);
		} else {
			roots.push(cat);
		}
	});

	const sortByName = (list) =>
		list.sort((a, b) =>
			a.name.localeCompare(b.name, undefined, { sensitivity: 'base' })
		);

	const flatten = (list, depth = 0) => {
		const result = [];
		sortByName(list).forEach((cat) => {
			result.push({
				label: `${'-'.repeat(depth)} ${cat.name}`.trim(),
				value: cat.slug,
			});
			result.push(...flatten(cat.children, depth + 1));
		});
		return result;
	};

	return flatten(roots);
}


export default function Edit({ attributes, setAttributes }) {
	const {
		category,
		tag,
		id,
		hstart,
		order,
		sort,
		lang,
		additional_class,
		color,
		style,
		masonry,
		hide_title,
		hide_accordion,
		registerstyle,
		register,
	} = attributes;
	const blockProps = useBlockProps({
		className: style === 'dark' ? 'is-style-dark' : undefined,
	});
	const [categorystate, setSelectedCategories] = useState(['']);
	const [tagstate, setSelectedTags] = useState(['']);
	const [idstate, setSelectedIDs] = useState(['']);

	const categories = useSelect((select) => {
		return select('core').getEntityRecords('taxonomy', 'bk_glossary_category', {
			per_page: -1,
			orderby: 'name',
			order: 'asc',
			status: 'publish',
			_fields: 'id,name,slug,parent',
		});
	}, []);

	const categoryoptions = [
		{
			label: __('all', 'wp-ai'),
			value: '',
		},
	];

	if (Array.isArray(categories)) {
		categoryoptions.push(...buildCategoryOptions(categories));
	}

	const tags = useSelect((select) => {
		return select('core').getEntityRecords('taxonomy', 'bk_glossary_tag', {
			per_page: -1,
			orderby: 'name',
			order: 'asc',
			status: 'publish',
			_fields: 'id,name,slug',
		});
	}, []);

	const tagoptions = [
		{
			label: __('all', 'wp-ai'),
			value: '',
		},
	];

	if (!!tags) {
		Object.values(tags).forEach((tag) => {
			tagoptions.push({
				label: tag.name,
				value: tag.slug,
			});
		});
	}

	const glossarys = useSelect((select) => {
		return select('core').getEntityRecords('postType', 'bk_glossary', {
			per_page: -1,
			orderby: 'title',
			order: 'asc',
			status: 'publish',
			_fields: 'id,title.rendered',
		});
	}, []);

	const glossaryoptions = [
		{
			label: __('all', 'wp-ai'),
			value: 0,
		},
	];

	if (!!glossarys) {
		Object.values(glossarys).forEach((glossary) => {
			glossaryoptions.push({
				label: glossary.title.rendered
					? glossary.title.rendered
					: __('No title', 'wp-ai'),
				value: glossary.id,
			});
		});
	}

	const langoptions = [
		{
			label: __('all', 'wp-ai'),
			value: '',
		},
		{
			label: __('German', 'wp-ai'),
			value: 'de',
		},
		{
			label: __('English', 'wp-ai'),
			value: 'en',
		},
		{
			label: __('French', 'wp-ai'),
			value: 'fr',
		},
		{
			label: __('Spanish', 'wp-ai'),
			value: 'es',
		},
		{
			label: __('Russian', 'wp-ai'),
			value: 'ru',
		},
		{
			label: __('Chinese', 'wp-ai'),
			value: 'zh',
		},
	];

	const registeroptions = [
		{
			label: __('none', 'wp-ai'),
			value: '',
		},
		{
			label: __('Categories', 'wp-ai'),
			value: 'category',
		},
		{
			label: __('Tags', 'wp-ai'),
			value: 'tag',
		},
	];

	const registerstyleoptions = [
		{
			label: __('A - Z', 'wp-ai'),
			value: 'a-z',
		},
		{
			label: __('Tagcloud', 'wp-ai'),
			value: 'tagcloud',
		},
		{
			label: __('Tabs', 'wp-ai'),
			value: 'tabs',
		},
		{
			label: __('-- hidden --', 'wp-ai'),
			value: '',
		},
	];

	const coloroptions = [
		{
			label: 'fau',
			value: 'fau',
		},
		{
			label: 'med',
			value: 'med',
		},
		{
			label: 'nat',
			value: 'nat',
		},
		{
			label: 'phil',
			value: 'phil',
		},
		{
			label: 'rw',
			value: 'rw',
		},
		{
			label: 'tf',
			value: 'tf',
		},
	];

	const styleoptions = [
		{
			label: __('none', 'wp-ai'),
			value: '',
		},
		{
			label: 'light',
			value: 'light',
		},
		{
			label: 'dark',
			value: 'dark',
		},
	];

	const sortoptions = [
		{
			label: __('Title', 'wp-ai'),
			value: 'title',
		},
		{
			label: __('ID', 'wp-ai'),
			value: 'id',
		},
		{
			label: __('Sort field', 'wp-ai'),
			value: 'sortfield',
		},
	];

	const orderoptions = [
		{
			label: __('ASC', 'wp-ai'),
			value: 'ASC',
		},
		{
			label: __('DESC', 'wp-ai'),
			value: 'DESC',
		},
	];

	//////// onChange handlers /////////
	const onChangeCategory = (newValues) => {
		setSelectedCategories(newValues);
		setAttributes({ category: String(newValues) });
	};

	const onChangeTag = (newValues) => {
		setSelectedTags(newValues);
		setAttributes({ tag: String(newValues) });
	};

	const onChangeID = (newValues) => {
		setSelectedIDs(newValues);
		setAttributes({ id: String(newValues) });
	};

	return (
		<>
			<BlockControls>
				<HeadingLevelDropdown
					options={[2, 3, 4, 5, 6]}
					value={hstart}
					onChange={(value) => setAttributes({ hstart: value })}
				/>
			</BlockControls>

			<InspectorControls>
				<PanelBody
					title={__('Filter options', 'wp-ai')}
					header={__('Filter the glossary-entries.', 'wp-ai')}
				>
					<SelectControl
						label={__('Categories', 'wp-ai')}
						help={__(
							'Only show glossary-entries with these selected categories.',
							'wp-ai'
						)}
						value={categorystate}
						options={categoryoptions}
						onChange={onChangeCategory}
						multiple
					/>
					<SelectControl
						label={__('Tags', 'wp-ai')}
						help={__(
							'Only show glossary-entries with these selected tags.',
							'wp-ai'
						)}
						value={tagstate}
						options={tagoptions}
						onChange={onChangeTag}
						multiple
					/>
					<SelectControl
						label={__('Single glossary-Entries', 'wp-ai')}
						help={__(
							'Only show these glossary-entries.',
							'wp-ai'
						)}
						value={idstate}
						options={glossaryoptions}
						onChange={onChangeID}
						multiple
					/>
					<SelectControl
						label={__('Language', 'wp-ai')}
						help={__(
							'Only show glossary-entries in this language.',
							'wp-ai'
						)}
						value={lang}
						options={langoptions}
						onChange={(value) =>
							setAttributes({ lang: value })
						}
					/>
					<SelectControl
						label={__('Group register Content by', 'wp-ai')}
						help={__(
							'Group glossary-entries by categories or tags.',
							'wp-ai'
						)}
						value={register}
						options={registeroptions}
						onChange={(value) =>
							setAttributes({ register: value })
						}
					/>
				</PanelBody>
				<PanelBody
					title={__('Appearance', 'wp-ai')}
					name={__('Appearance', 'wp-ai')}
					icon="admin-appearance"
					initialOpen={false}
				>
					<SelectControl
						label={__('register style', 'wp-ai')}
						options={registerstyleoptions}
						onChange={(value) =>
							setAttributes({ registerstyle: value })
						}
					/>
						<>
							<ToggleControl
								checked={!!hide_accordion}
								label={__('Hide accordion', 'wp-ai')}
								onChange={() =>
									setAttributes({
										hide_accordion: !hide_accordion,
									})
								}
							/>
								<>
									<ToggleControl
										checked={!!masonry}
										label={__('Grid', 'wp-ai')}
										onChange={() =>
											setAttributes({
												masonry: !masonry,
											})
										}
									/>
									<SelectControl
										label={__(
											'Accordion-Style',
											'wp-ai'
										)}
										value={style || 'light'}
										options={styleoptions}
										onChange={(value) =>
											setAttributes({ style: value })
										}
									/>
									<SelectControl
										label={__('Color', 'wp-ai')}
										value={color || ''}
										options={coloroptions}
										onChange={(value) =>
											setAttributes({ color: value })
										}
									/>
								</>
								<ToggleControl
									checked={!!hide_title}
									label={__('Hide title', 'wp-ai')}
									onChange={() =>
										setAttributes({
											hide_title: !hide_title,
										})
									}
								/>
						</>
				</PanelBody>
				<PanelBody title={__('Sorting options', 'wp-ai')}>
					<SelectControl
						label={__('Sort', 'wp-ai')}
						options={sortoptions}
						onChange={(value) =>
							setAttributes({ sort: value })
						}
					/>
					<SelectControl
						label={__('Order', 'wp-ai')}
						options={orderoptions}
						onChange={(value) =>
							setAttributes({ order: value })
						}
					/>
				</PanelBody>
			</InspectorControls>
			<div {...blockProps}>
				<ServerSideRender
					block="wp-ai/glossary"
					attributes={attributes}
				/>
			</div>
		</>
	);
}
