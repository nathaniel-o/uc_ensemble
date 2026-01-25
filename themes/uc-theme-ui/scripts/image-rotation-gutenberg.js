
(function() {
    const { registerPlugin } = wp.plugins;
    const { PluginDocumentSettingPanel } = wp.editPost;
    const { useState, useEffect } = wp.element;
    const { PanelRow, RangeControl, Button, ButtonGroup } = wp.components;
    const { select, dispatch, subscribe } = wp.data;
    const { createHigherOrderComponent } = wp.compose;
    
    // Add custom attribute to image block
    wp.hooks.addFilter(
        "blocks.registerBlockType",
        "uc-theme/image-rotation",
        function(settings, name) {
            if (name !== "core/image") {
                return settings;
            }
            
            return Object.assign({}, settings, {
                attributes: Object.assign({}, settings.attributes, {
                    ucRotation: {
                        type: "number",
                        default: 0
                    }
                })
            });
        }
    );
    
    // Add custom styles to block
    const withImageRotation = createHigherOrderComponent((BlockListBlock) => {
        return (props) => {
            if (props.name !== "core/image") {
                return <BlockListBlock {...props} />;
            }
            
            const { attributes } = props;
            const rotation = attributes.ucRotation || 0;
            
            if (rotation === 0) {
                return <BlockListBlock {...props} />;
            }
            
            // Get the DOM node
            const blockRef = wp.element.useRef();
            
            useEffect(() => {
                if (blockRef.current) {
                    const imgElement = blockRef.current.querySelector("img");
                    if (imgElement) {
                        // Remove existing rotation classes
                        imgElement.classList.remove("rotate-90", "rotate-180", "rotate-270", "rotate-custom");
                        
                        // Apply appropriate rotation
                        if ([90, 180, 270].includes(rotation)) {
                            imgElement.classList.add(`rotate-${rotation}`);
                        } else {
                            imgElement.classList.add("rotate-custom");
                            imgElement.style.setProperty("--rotation-angle", `${rotation}deg`);
                        }
                    }
                }
            });
            
            return (
                <div ref={blockRef}>
                    <BlockListBlock {...props} />
                </div>
            );
        };
    }, "withImageRotation");
    
    wp.hooks.addFilter(
        "editor.BlockListBlock",
        "uc-theme/with-image-rotation",
        withImageRotation
    );
    
    // Add controls to the sidebar
    function ImageRotationControls() {
        const selectedBlock = select("core/block-editor").getSelectedBlock();
        
        if (!selectedBlock || selectedBlock.name !== "core/image") {
            return null;
        }
        
        const { ucRotation = 0 } = selectedBlock.attributes;
        const [rotation, setRotation] = useState(ucRotation);
        
        const applyRotation = (value) => {
            setRotation(value);
            dispatch("core/block-editor").updateBlockAttributes(
                selectedBlock.clientId, 
                { ucRotation: value }
            );
            
            // Save to image metadata when a specific rotation is applied
            if ([0, 90, 180, 270].includes(value) || value % 15 === 0) {
                // Find the image ID
                const imageId = selectedBlock.attributes.id;
                if (imageId) {
                    // Save rotation to attachment via AJAX
                    wp.apiFetch({
                        path: "/wp/v2/media/" + imageId,
                        method: "POST",
                        data: {
                            meta: {
                                uc_rotation: value
                            }
                        }
                    });
                }
            }
        };
        
        return (
            <PluginDocumentSettingPanel
                name="uc-image-rotation-panel"
                title="Image Rotation"
                className="uc-image-rotation-panel"
            >
                <PanelRow>
                    <ButtonGroup>
                        <Button 
                            isPrimary={rotation === 0} 
                            onClick={() => applyRotation(0)}
                            isSmall
                        >
                            0°
                        </Button>
                        <Button 
                            isPrimary={rotation === 90} 
                            onClick={() => applyRotation(90)}
                            isSmall
                        >
                            90°
                        </Button>
                        <Button 
                            isPrimary={rotation === 180} 
                            onClick={() => applyRotation(180)}
                            isSmall
                        >
                            180°
                        </Button>
                        <Button 
                            isPrimary={rotation === 270} 
                            onClick={() => applyRotation(270)}
                            isSmall
                        >
                            270°
                        </Button>
                    </ButtonGroup>
                </PanelRow>
                <RangeControl
                    label="Custom Rotation"
                    value={rotation}
                    onChange={applyRotation}
                    min={0}
                    max={359}
                />
            </PluginDocumentSettingPanel>
        );
    }
    
    registerPlugin("uc-image-rotation", {
        render: ImageRotationControls
    });
})();