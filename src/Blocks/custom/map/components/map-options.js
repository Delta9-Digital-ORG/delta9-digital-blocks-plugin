import React from 'react';
import { __ } from '@wordpress/i18n';
import { PanelBody, TextControl, Button } from '@wordpress/components';
import { MediaPlaceholder } from '@wordpress/block-editor';
import {
	Collapsable,
	Control,
	IconLabel,
	IconToggle,
	Notification,
	NumberPicker,
	OptionSelector,
	Repeater,
	RepeaterItem,
	Section,
	checkAttr,
	getAttrKey,
	icons,
	truncateMiddle,
} from '@eightshift/frontend-libs/scripts';
import manifest from '../manifest.json';

export const MapOptions = ({ attributes, setAttributes }) => {

	const mapLayers = checkAttr('mapLayers', attributes, manifest);
	const mapCenterLat = checkAttr('mapCenterLat', attributes, manifest);
	const mapCenterLon = checkAttr('mapCenterLon', attributes, manifest);
	const mapZoom = checkAttr('mapZoom', attributes, manifest);
	const mapInteractions = checkAttr('mapInteractions', attributes, manifest);
	const mapControls = checkAttr('mapControls', attributes, manifest);

	const layerTypes = {
		openStreetMap: {
			icon: icons.mapLayer,
			title: __('OpenStreetMap', 'delta9-digital-blocks-plugin'),
		},
		vectorJson: {
			icon: icons.mapLayerJson, title: __('Vector map', 'delta9-digital-blocks-plugin'),
			subtitle: __('with JSON styles', 'delta9-digital-blocks-plugin'),
		},
		mapBoxVector: {
			icon: icons.mapLayerVector, title: __('Mapbox map', 'delta9-digital-blocks-plugin'),
			subtitle: __('Vector tiles', 'delta9-digital-blocks-plugin'),
		},
		mapBoxRaster: {
			icon: icons.mapLayerRaster, title: __('Mapbox map', 'delta9-digital-blocks-plugin'),
			subtitle: __('Raster tiles', 'delta9-digital-blocks-plugin'),
		},
		mapTilerVector: {
			icon: icons.mapLayerVector, title: __('MapTiler tiles', 'delta9-digital-blocks-plugin'),
			subtitle: __('Vector - XYZ (PBF)', 'delta9-digital-blocks-plugin'),
		},
		mapTilerRasterXyz: {
			icon: icons.mapLayerRaster, title: __('MapTiler map/tiles', 'delta9-digital-blocks-plugin'),
			subtitle: __('Raster - XYZ', 'delta9-digital-blocks-plugin'),
		},
		mapTilerRasterJson: {
			icon: icons.mapLayerRaster, title: __('MapTiler map/tiles', 'delta9-digital-blocks-plugin'),
			subtitle: __('Raster - JSON', 'delta9-digital-blocks-plugin'),
		},
		geoJson: { icon: icons.fileMetadata, title: __('GeoJSON', 'delta9-digital-blocks-plugin') },
	};

	return (
		<PanelBody title={__('Map', 'delta9-digital-blocks-plugin')}>
			<Section icon={icons.play} label={__('Initial view', 'delta9-digital-blocks-plugin')}>
				<Control icon={icons.alignHorizontalVerticalAlt} label={__('Center point', 'delta9-digital-blocks-plugin')} additionalLabelClasses='es-mb-1!'>
					<div className='es-fifty-fifty-h'>
						<TextControl
							label={__('Lat', 'delta9-digital-blocks-plugin')}
							value={mapCenterLat}
							onChange={(value) => setAttributes({ [getAttrKey('mapCenterLat', attributes, manifest)]: value })}
							className='es-m-0! es-m-0-bcf!'
						/>

						<TextControl
							label={__('Lon', 'delta9-digital-blocks-plugin')}
							value={mapCenterLon}
							onChange={(value) => setAttributes({ [getAttrKey('mapCenterLon', attributes, manifest)]: value })}
							className='es-m-0! es-m-0-bcf!'
						/>
					</div>
				</Control>

				<NumberPicker
					icon={icons.search}
					label={__('Zoom', 'delta9-digital-blocks-plugin')}
					value={mapZoom}
					onChange={(value) => setAttributes({ [getAttrKey('mapZoom', attributes, manifest)]: value })}
					min={1}
					max={30}
					additionalClasses='es-flex-shrink-0'
					inlineLabel
				/>
			</Section>

			<Section icon={icons.options} label={__('Configuration', 'delta9-digital-blocks-plugin')} noBottomSpacing>
				<Repeater
					icon={icons.layers}
					label={__('Layers', 'delta9-digital-blocks-plugin')}

					items={mapLayers}
					attributeName={getAttrKey('mapLayers', attributes, manifest)}
					setAttributes={setAttributes}
				>
					{mapLayers.map((layer, index) => {
						// eslint-disable-next-line max-len
						const needsApiKey = ['mapBoxVector', 'mapBoxRaster', 'mapTilerVector', 'vectorJson', 'mapTilerRasterXyz', 'mapTilerRasterJson'].includes(layer?.type);
						// eslint-disable-next-line max-len
						const hasMapStyleOptions = ['mapBoxVector', 'mapBoxRaster', 'mapTilerVector', 'vectorJson', 'mapTilerRasterXyz', 'mapTilerRasterJson'].includes(layer?.type);

						return (
							<RepeaterItem
								key={layer.id}
								icon={layer?.type ? layerTypes?.[layer?.type]?.icon ?? icons.mapLayer : icons.layerOff}
								title={layerTypes?.[layer?.type]?.title ?? __('New layer', 'delta9-digital-blocks-plugin')}
								subtitle={
									layer?.type === 'geoJson'
										? truncateMiddle(layer?.geoJsonUrl?.slice(layer?.geoJsonUrl?.lastIndexOf('/') + 1) ?? '', 20)
										: layerTypes?.[layer?.type]?.subtitle
								}
								preIcon={
									layer?.type?.length < 1 ? icons.dummySpacer : (
										<Button
											icon={mapLayers[index]?.hidden ? icons.dummySpacer : icons.visible}
											onClick={() => {
												const modifiedData = [...mapLayers];
												modifiedData[index].hidden = !modifiedData[index].hidden;
												setAttributes({ [getAttrKey('mapLayers', attributes, manifest)]: modifiedData });
											}}
											// eslint-disable-next-line max-len
											className='es-mr-1 es-button-square-20 es-button-icon-16 es-rounded-1 es-border-cool-gray-300 es-hover-border-cool-gray-300 es-focus-border-transparent es-transition-colors'
											label={mapLayers[index]?.hidden ? __('Show', 'delta9-digital-blocks-plugin') : __('Hide', 'delta9-digital-blocks-plugin')}
											showTooltip
										/>
									)
								}
							>
								{!layer?.type &&
									<OptionSelector
										label={__('Layer type', 'delta9-digital-blocks-plugin')}
										options={Object.entries(layerTypes).map(([value, { icon, title, subtitle }]) => ({
											value: value,
											label: title,
											icon: icon,
											subtitle: subtitle,
										}))}
										onChange={(value) => {
											const modifiedData = [...mapLayers];
											modifiedData[index].type = value;
											setAttributes({ [getAttrKey('mapLayers', attributes, manifest)]: modifiedData });
										}}
										alignment='vertical'
										noBottomSpacing
									/>
								}

								{needsApiKey &&
									<TextControl
										label={<IconLabel icon={icons.key} label={__('API key', 'delta9-digital-blocks-plugin')} />}
										value={mapLayers[index]?.apiKey}
										onChange={(value) => {
											const modifiedData = [...mapLayers];
											modifiedData[index].apiKey = value;
											setAttributes({ [getAttrKey('mapLayers', attributes, manifest)]: modifiedData });
										}}
									/>
								}

								{hasMapStyleOptions &&
									<TextControl
										label={<IconLabel icon={icons.color} label={__('Map style', 'delta9-digital-blocks-plugin')} />}
										value={mapLayers[index]?.styleUrl}
										onChange={(value) => {
											const modifiedData = [...mapLayers];
											modifiedData[index].styleUrl = value;
											setAttributes({ [getAttrKey('mapLayers', attributes, manifest)]: modifiedData });
										}}
										help={
											<>
												{!layer?.type?.startsWith('mapBox') &&
													__('Copy the full style URL from MapTiler. Keep the API key inside the URL.', 'delta9-digital-blocks-plugin')
												}

												{layer?.type === 'mapBoxVector' &&
													__('Copy the full style URL from Mapbox.', 'delta9-digital-blocks-plugin')
												}

												{layer?.type === 'mapBoxRaster' &&
													// eslint-disable-next-line max-len
													__('Copy the full style URL from Mapbox or a Mapbox-compatible source. Keep the access token inside the URL.', 'delta9-digital-blocks-plugin')
												}

												<br />
												<br />

												{['mapBoxRaster', 'mapTilerVector', 'mapTilerRasterXyz'].includes(layer?.type) &&
													<>
														<code className='es-bg-transparent es-p-0 es-text-3'>{'{z}/{x}/{y}'}</code>
														{
															// eslint-disable-next-line max-len
															__("should be left as they are in the URL; they're needed for the map to work properly.", 'delta9-digital-blocks-plugin')
														}
														<br />
														<br />
													</>
												}

												{__('Example', 'delta9-digital-blocks-plugin')}:
												<br />
												<span className='es-word-break-all'>
													{['mapTilerRasterJson', 'vectorJson'].includes(layer?.type) &&
														'https://api.maptiler.com/maps/{styleName}/tiles.json?key={apiKey}'
													}

													{layer?.type === 'mapTilerVector' && 'https://api.maptiler.com/tiles/v3/{z}/{x}/{y}.pbf?key={apiKey}'}

													{layer?.type === 'mapBoxVector' &&
														<>
															{'mapbox://styles/{styleName},'}
															<br />
															{'mapbox://styles/{userId}/{styleId}'}
														</>
													}

													{layer?.type === 'mapBoxRaster' &&
														'https://api.mapbox.com/v4/{tilesetId}/{z}/{x}/{y}[@2x].{imageFormat}?acess_token={apiKey}'
													}

													{layer?.type === 'mapTilerRasterXyz' &&
														'https://api.maptiler.com/maps/{styleName}/{z}/{x}/{y}.png?key={apiKey}'
													}
												</span>
											</>
										}
										className='es-m-0! es-mb-0-bcf!'
									/>
								}

								{layer?.type === 'geoJson' && (layer?.geoJsonUrl?.length < 1 || !layer?.geoJsonUrl) &&
									<MediaPlaceholder
										icon={icons.file}
										accept={['.json', '.geojson']}
										labels={{ title: __('GeoJSON file', 'delta9-digital-blocks-plugin') }}
										onSelect={
											(file) => {
												const modifiedData = [...mapLayers];
												modifiedData[index].geoJsonUrl = file.url;
												setAttributes({ [getAttrKey('mapLayers', attributes, manifest)]: modifiedData });
											}
										}
									/>
								}

								{layer?.type === 'geoJson' && layer?.geoJsonUrl?.length > 0 &&
									<div className='es-h-between'>
										<IconLabel
											icon={icons.file}
											label={
												<code className='es-word-break-all es-bg-transparent es-p-0 es-text-2.75!'>
													{layer.geoJsonUrl.slice(layer.geoJsonUrl.lastIndexOf('/') + 1)}
												</code>
											}
											additionalClasses='es-flex-shrink-1'
											standalone
										/>

										<Button
											onClick={() => {
												const modifiedData = [...mapLayers];
												delete modifiedData[index].geoJsonUrl;
												setAttributes({ [getAttrKey('mapLayers', attributes, manifest)]: modifiedData });
											}}
											// eslint-disable-next-line max-len
											className='es-button-icon-24 es-border-cool-gray-100 es-hover-border-cool-gray-200 es-hover-color-admin-accent es-rounded-1.5 es-nested-color-cool-gray-650'
										>
											{__('Replace', 'delta9-digital-blocks-plugin')}
										</Button>
									</div>
								}
							</RepeaterItem>
						);
					})}
				</Repeater>

				<Collapsable label={__('Controls', 'delta9-digital-blocks-plugin')} icon={icons.buttonOutline}>
					<IconToggle
						icon={icons.tag}
						label={__('Attribution', 'delta9-digital-blocks-plugin')}
						checked={mapControls.attribution}
						onChange={(value) => {
							const newValue = { ...mapControls };
							newValue.attribution = value;

							setAttributes({ [getAttrKey('mapControls', attributes, manifest)]: newValue });
						}}
					/>

					<IconToggle
						icon={icons.expandXl}
						label={__('Full screen', 'delta9-digital-blocks-plugin')}
						checked={mapControls.fullScreen}
						onChange={(value) => {
							const newValue = { ...mapControls };
							newValue.fullScreen = value;

							setAttributes({ [getAttrKey('mapControls', attributes, manifest)]: newValue });
						}}
					/>

					<IconToggle
						icon={icons.mouseCursor}
						label={__('Pointer position', 'delta9-digital-blocks-plugin')}
						checked={mapControls.mousePosition}
						onChange={(value) => {
							const newValue = { ...mapControls };
							newValue.mousePosition = value;

							setAttributes({ [getAttrKey('mapControls', attributes, manifest)]: newValue });
						}}
					/>

					<IconToggle
						icon={icons.mapPin}
						label={__('Minimap', 'delta9-digital-blocks-plugin')}
						checked={mapControls.overviewMap}
						onChange={(value) => {
							const newValue = { ...mapControls };
							newValue.overviewMap = value;

							setAttributes({ [getAttrKey('mapControls', attributes, manifest)]: newValue });
						}}
					/>

					<IconToggle
						icon={icons.rotateLeft}
						label={__('Reset rotation', 'delta9-digital-blocks-plugin')}
						checked={mapControls.rotate}
						onChange={(value) => {
							const newValue = { ...mapControls };
							newValue.rotate = value;

							setAttributes({ [getAttrKey('mapControls', attributes, manifest)]: newValue });
						}}
					/>

					<IconToggle
						icon={icons.ruler}
						label={__('Map scale', 'delta9-digital-blocks-plugin')}
						checked={mapControls.scaleLine}
						onChange={(value) => {
							const newValue = { ...mapControls };
							newValue.scaleLine = value;

							setAttributes({ [getAttrKey('mapControls', attributes, manifest)]: newValue });
						}}
					/>

					<IconLabel icon={icons.search} label={__('Zoom', 'delta9-digital-blocks-plugin')} additionalClasses='es-mb-1.5 es-font-weight-500' standalone />

					<IconToggle
						icon={icons.dummySpacer}
						label={__('Slider', 'delta9-digital-blocks-plugin')}
						checked={mapControls.zoomSlider}
						onChange={(value) => {
							const newValue = { ...mapControls };
							newValue.zoomSlider = value;

							setAttributes({ [getAttrKey('mapControls', attributes, manifest)]: newValue });
						}}
						reducedBottomSpacing
					/>

					<IconToggle
						icon={icons.dummySpacer}
						label={__('Buttons', 'delta9-digital-blocks-plugin')}
						checked={mapControls.zoom}
						onChange={(value) => {
							const newValue = { ...mapControls };
							newValue.zoom = value;

							setAttributes({ [getAttrKey('mapControls', attributes, manifest)]: newValue });
						}}
						reducedBottomSpacing
					/>

					<IconToggle
						icon={icons.dummySpacer}
						label={__('To extent', 'delta9-digital-blocks-plugin')}
						checked={mapControls.zoomToExtent}
						onChange={(value) => {
							const newValue = { ...mapControls };
							newValue.zoomToExtent = value;

							setAttributes({ [getAttrKey('mapControls', attributes, manifest)]: newValue });
						}}
					/>

					<Notification
						type='info'
						text={__('Note', 'delta9-digital-blocks-plugin')}
						subtitle={__('Some of the options might not be reflected in the editor', 'delta9-digital-blocks-plugin')}
						noBottomSpacing
					/>
				</Collapsable>

				<Collapsable label={__('Interactions', 'delta9-digital-blocks-plugin')} icon={icons.pointerHand} noBottomSpacing>
					<IconToggle
						icon={icons.focus}
						label={__('Only when map is focused', 'delta9-digital-blocks-plugin')}
						checked={mapInteractions.onFocusOnly}
						onChange={(value) => {
							const newValue = { ...mapInteractions };
							newValue.onFocusOnly = value;

							setAttributes({ [getAttrKey('mapInteractions', attributes, manifest)]: newValue });
						}}
					/>

					<IconLabel
						icon={icons.rotateRight}
						label={__('Rotate', 'delta9-digital-blocks-plugin')}
						additionalClasses='es-mb-1.5 es-font-weight-500'
						standalone
					/>

					<IconToggle
						icon={icons.dummySpacer}
						label={__('Alt+Shift and drag to rotate', 'delta9-digital-blocks-plugin')}
						checked={mapInteractions.altShiftDragRotate}
						onChange={(value) => {
							const newValue = { ...mapInteractions };
							newValue.altShiftDragRotate = value;

							setAttributes({ [getAttrKey('mapInteractions', attributes, manifest)]: newValue });
						}}
						reducedBottomSpacing
					/>

					<IconToggle
						icon={icons.dummySpacer}
						label={__('Pinch to rotate', 'delta9-digital-blocks-plugin')}
						checked={mapInteractions.pinchRotate}
						onChange={(value) => {
							const newValue = { ...mapInteractions };
							newValue.pinchRotate = value;

							setAttributes({ [getAttrKey('mapInteractions', attributes, manifest)]: newValue });
						}}
					/>

					<IconLabel icon={icons.search} label={__('Zoom', 'delta9-digital-blocks-plugin')} additionalClasses='es-mb-1.5 es-font-weight-500' standalone />
					<IconToggle
						icon={icons.dummySpacer}
						label={__('Double-click to zoom', 'delta9-digital-blocks-plugin')}
						checked={mapInteractions.doubleClickZoom}
						onChange={(value) => {
							const newValue = { ...mapInteractions };
							newValue.doubleClickZoom = value;

							setAttributes({ [getAttrKey('mapInteractions', attributes, manifest)]: newValue });
						}}
						reducedBottomSpacing
					/>

					<IconToggle
						icon={icons.dummySpacer}
						label={__('Zoom with mousewheel', 'delta9-digital-blocks-plugin')}
						checked={mapInteractions.mouseWheelZoom}
						onChange={(value) => {
							const newValue = { ...mapInteractions };
							newValue.mouseWheelZoom = value;

							setAttributes({ [getAttrKey('mapInteractions', attributes, manifest)]: newValue });
						}}
						reducedBottomSpacing
					/>

					<IconToggle
						icon={icons.dummySpacer}
						label={__('Shift and drag to zoom', 'delta9-digital-blocks-plugin')}
						checked={mapInteractions.shiftDragZoom}
						onChange={(value) => {
							const newValue = { ...mapInteractions };
							newValue.shiftDragZoom = value;

							setAttributes({ [getAttrKey('mapInteractions', attributes, manifest)]: newValue });
						}}
						reducedBottomSpacing
					/>

					<IconToggle
						icon={icons.dummySpacer}
						label={__('Pinch to zoom', 'delta9-digital-blocks-plugin')}
						checked={mapInteractions.pinchZoom}
						onChange={(value) => {
							const newValue = { ...mapInteractions };
							newValue.pinchZoom = value;

							setAttributes({ [getAttrKey('mapInteractions', attributes, manifest)]: newValue });
						}}
						reducedBottomSpacing
					/>

					<NumberPicker
						icon={icons.dummySpacer}
						label={__('Animation duration (ms)', 'delta9-digital-blocks-plugin')}
						value={mapInteractions.zoomDuration}
						onChange={(value) => {
							const newValue = { ...mapInteractions };
							newValue.zoomDuration = value;

							setAttributes({ [getAttrKey('mapInteractions', attributes, manifest)]: newValue });
						}}
						min={0}
						max={10000}
						reducedBottomSpacing
						inlineLabel
					/>

					<NumberPicker
						icon={icons.dummySpacer}
						label={__('Zoom-in step', 'delta9-digital-blocks-plugin')}
						value={mapInteractions.zoomDelta}
						onChange={(value) => {
							const newValue = { ...mapInteractions };
							newValue.zoomDelta = value;

							setAttributes({ [getAttrKey('mapInteractions', attributes, manifest)]: newValue });
						}}
						inlineLabel
						min={1}
						max={10}
					/>

					<IconToggle
						icon={icons.keyboard}
						label={__('Keyboard interactions', 'delta9-digital-blocks-plugin')}
						checked={mapInteractions.keyboard}
						onChange={(value) => {
							const newValue = { ...mapInteractions };
							newValue.keyboard = value;

							setAttributes({ [getAttrKey('mapInteractions', attributes, manifest)]: newValue });
						}}
					/>

					<IconToggle
						icon={icons.cursorMove}
						label={__('Drag to move map', 'delta9-digital-blocks-plugin')}
						checked={mapInteractions.dragPan}
						onChange={(value) => {
							const newValue = { ...mapInteractions };
							newValue.dragPan = value;

							setAttributes({ [getAttrKey('mapInteractions', attributes, manifest)]: newValue });
						}}
					/>

					<Notification
						type='info'
						text={__('Note', 'delta9-digital-blocks-plugin')}
						subtitle={__('Some of the options might not be reflected in the editor', 'delta9-digital-blocks-plugin')}
						noBottomSpacing
					/>
				</Collapsable>
			</Section>
		</PanelBody>
	);
};
