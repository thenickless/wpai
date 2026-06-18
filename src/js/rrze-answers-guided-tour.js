/**
 * WP AI admin tours: overview Guide + contextual setup tour.
 */
import { useEffect, useState } from '@wordpress/element';
import { render } from '@wordpress/element';
import { Guide } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { SetupTour } from './setup-tour';

function GuideIcon( { dashicon } ) {
	return (
		<div className="wp-ai-guided-tour__icon" aria-hidden="true">
			<span className={ `dashicons ${ dashicon }` } />
		</div>
	);
}

function dismissTour() {
	if ( typeof BKWPAIGuide === 'undefined' ) {
		return Promise.resolve();
	}

	const body = new FormData();
	body.append( 'action', 'wp_ai_dismiss_guided_tour' );
	body.append( 'nonce', BKWPAIGuide.nonce );

	return fetch( BKWPAIGuide.ajaxUrl, {
		method: 'POST',
		body,
		credentials: 'same-origin',
	} );
}

function ToursApp( { autoStartGuide, autoStartSetup, setupTourStepId } ) {
	const setupTourActive =
		Boolean( autoStartSetup ) || setupTourStepId.length > 0;
	const [ guideOpen, setGuideOpen ] = useState(
		Boolean( autoStartGuide ) && ! setupTourActive
	);
	const [ setupOpen, setSetupOpen ] = useState( setupTourActive );
	const [ setupTourKey, setSetupTourKey ] = useState( 0 );
	const [ setupStepId, setSetupStepId ] = useState( setupTourStepId );

	useEffect( () => {
		const guideButton = document.getElementById(
			'wp-ai-start-guided-tour'
		);
		const setupButton = document.getElementById(
			'wp-ai-start-setup-tour'
		);

		const openGuide = () => {
			setSetupOpen( false );
			setGuideOpen( true );
		};
		const openSetup = () => {
			setGuideOpen( false );
			setSetupStepId( '' );
			setSetupTourKey( ( key ) => key + 1 );
			setSetupOpen( true );
		};

		guideButton?.addEventListener( 'click', openGuide );
		setupButton?.addEventListener( 'click', openSetup );

		return () => {
			guideButton?.removeEventListener( 'click', openGuide );
			setupButton?.removeEventListener( 'click', openSetup );
		};
	}, [] );

	const finishGuide = () => {
		setGuideOpen( false );
		dismissTour();
	};

	const guidePages = [
		{
			image: <GuideIcon dashicon="dashicons-welcome-learn-more" />,
			content: (
				<>
					<h1 className="wp-ai-guided-tour__heading">
						{ __( 'Welcome to WP AI', 'wp-ai' ) }
					</h1>
					<p className="wp-ai-guided-tour__text">
						{ __(
							'This plugin helps you manage FAQ entries, glossary terms, synonyms, and placeholders — and embed them in pages with blocks or shortcodes.',
							'wp-ai'
						) }
					</p>
				</>
			),
		},
		{
			image: <GuideIcon dashicon="dashicons-editor-help" />,
			content: (
				<>
					<h1 className="wp-ai-guided-tour__heading">
						{ __( 'Create and manage content', 'wp-ai' ) }
					</h1>
					<p className="wp-ai-guided-tour__text">
						{ __(
							'Use the FAQ and Glossary menus in the WordPress admin to add questions, terms, categories, and tags.',
							'wp-ai'
						) }
					</p>
				</>
			),
		},
		{
			image: <GuideIcon dashicon="dashicons-cloud" />,
			content: (
				<>
					<h1 className="wp-ai-guided-tour__heading">
						{ __( 'Import from other sites', 'wp-ai' ) }
					</h1>
					<p className="wp-ai-guided-tour__text">
						{ __(
							'Use the interactive setup tour to register domains, select categories, and synchronize FAQ and glossary content.',
							'wp-ai'
						) }
					</p>
				</>
			),
		},
		{
			image: <GuideIcon dashicon="dashicons-media-text" />,
			content: (
				<>
					<h1 className="wp-ai-guided-tour__heading">
						{ __( 'Logfile and blocks', 'wp-ai' ) }
					</h1>
					<p className="wp-ai-guided-tour__text">
						{ __(
							'After each sync, details are written to the logfile. Insert FAQ and glossary blocks in the editor or use shortcodes.',
							'wp-ai'
						) }
					</p>
				</>
			),
		},
	];

	return (
		<>
			{ guideOpen && (
				<Guide
					className="wp-ai-guided-tour"
					contentLabel={ __(
						'WP AI guided tour',
						'wp-ai'
					) }
					finishButtonText={ __( 'Get started', 'wp-ai' ) }
					onFinish={ finishGuide }
					pages={ guidePages }
				/>
			) }
			{ setupOpen && (
				<SetupTour
					key={ setupTourKey }
					initialStepId={ setupStepId }
					onClose={ () => setSetupOpen( false ) }
				/>
			) }
		</>
	);
}

const root = document.getElementById( 'wp-ai-guided-tour-root' );

if ( root && typeof BKWPAIGuide !== 'undefined' ) {
	render(
		<ToursApp
			autoStartGuide={ BKWPAIGuide.autoStart }
			autoStartSetup={ BKWPAIGuide.autoStartSetup }
			setupTourStepId={ BKWPAIGuide.setupTourStepId }
		/>,
		root
	);
}
