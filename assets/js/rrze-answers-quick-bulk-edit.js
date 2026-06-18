/* global inlineEditPost, BKWPAIQuickBulkEdit */
(function ($) {
	'use strict';

	const fieldName =
		(typeof BKWPAIQuickBulkEdit !== 'undefined' &&
			BKWPAIQuickBulkEdit.fieldName) ||
		'wp_ai_lang';

	function getLangForPost(postId) {
		const $postRow = $('#post-' + postId);
		const $langCell = $postRow.find('.column-lang .wp-ai-inline-lang');

		if ($langCell.length) {
			return $langCell.text().trim();
		}

		return '';
	}

	function populateQuickEdit(postId) {
		const lang = getLangForPost(postId);
		const $editRow = $('#edit-' + postId);
		const $select = $editRow.find('select.wp-ai-lang, select[name="' + fieldName + '"]');

		if ($select.length && lang !== '') {
			$select.val(lang);
		}
	}

	if (typeof inlineEditPost !== 'undefined') {
		const inlineEdit = inlineEditPost.edit;
		inlineEditPost.edit = function (id) {
			inlineEdit.apply(this, arguments);

			let postId = 0;
			if (typeof id === 'object') {
				postId = parseInt(this.getId(id), 10);
			}

			if (postId > 0) {
				populateQuickEdit(postId);
			}
		};
	}

	// Bulk edit uses the list-table form (method="get"). Switch to POST so custom
	// fields like wp_ai_lang are not dropped from long FAQ query strings.
	$('#posts-filter').on('submit', function () {
		const $bulkEdit = $('#bulk-edit');
		if (!$bulkEdit.length || !$bulkEdit.is(':visible')) {
			return;
		}

		this.method = 'post';

		const $selects = $bulkEdit.find(
			'select.wp-ai-lang, select[name="' + fieldName + '"]'
		);
		const $chosen = $selects
			.filter(function () {
				return $(this).val() !== '-1';
			})
			.last();

		if ($chosen.length) {
			$selects.not($chosen).prop('disabled', true);
		}
	});
})(jQuery);
