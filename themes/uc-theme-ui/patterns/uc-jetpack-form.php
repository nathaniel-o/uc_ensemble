<?php
/**
 * Title: UC Jetpack Form
 * Slug: uc-jetpack-form
 * Categories: forms
 * Description: Contact form that falls back to a styled placeholder if Jetpack is unavailable
 */

// Check if Jetpack contact-form block is available
$jetpack_available = WP_Block_Type_Registry::get_instance()->is_registered( 'jetpack/contact-form' );

if ( $jetpack_available ) :
?>
<!-- wp:jetpack/contact-form {"confirmationType":"text","jetpackCRM":false,"variationName":"default","salesforceData":{"organizationId":"","sendToSalesforce":false},"mailpoet":{"listId":null,"listName":null,"enabledForForm":false}} -->
<div class="wp-block-jetpack-contact-form"><!-- wp:jetpack/field-name {"required":true} -->
<div><!-- wp:jetpack/label {"label":"Name"} /-->

<!-- wp:jetpack/input /--></div>
<!-- /wp:jetpack/field-name -->

<!-- wp:jetpack/field-email {"required":true} -->
<div><!-- wp:jetpack/label {"label":"Email"} /-->

<!-- wp:jetpack/input /--></div>
<!-- /wp:jetpack/field-email -->

<!-- wp:jetpack/field-textarea -->
<div><!-- wp:jetpack/label {"label":"Message"} /-->

<!-- wp:jetpack/input {"type":"textarea"} /--></div>
<!-- /wp:jetpack/field-textarea -->

<!-- wp:button {"tagName":"button","type":"submit","lock":{"move":false,"remove":true}} -->
<div class="wp-block-button"><button type="submit" class="wp-block-button__link wp-element-button">Contact us</button></div>
<!-- /wp:button --></div>
<!-- /wp:jetpack/contact-form -->
<?php else : ?>
<!-- Placeholder form: same structure/classes as Jetpack, no functionality -->
<!-- wp:group {"className":"wp-block-jetpack-contact-form jetpack-placeholder","layout":{"type":"default"}} -->
<div class="wp-block-group wp-block-jetpack-contact-form jetpack-placeholder">
	
	<!-- wp:group {"className":"jetpack-field-name jetpack-field","layout":{"type":"default"}} -->
	<div class="wp-block-group jetpack-field-name jetpack-field">
		<label class="jetpack-field-label">Name <span class="required">*</span></label>
		<input type="text" class="jetpack-field-input" placeholder="Name" disabled title="Contact form unavailable – please email us directly" />
	</div>
	<!-- /wp:group -->

	<!-- wp:group {"className":"jetpack-field-email jetpack-field","layout":{"type":"default"}} -->
	<div class="wp-block-group jetpack-field-email jetpack-field">
		<label class="jetpack-field-label">Email <span class="required">*</span></label>
		<input type="email" class="jetpack-field-input" placeholder="Email" disabled title="Contact form unavailable – please email us directly" />
	</div>
	<!-- /wp:group -->

	<!-- wp:group {"className":"jetpack-field-textarea jetpack-field","layout":{"type":"default"}} -->
	<div class="wp-block-group jetpack-field-textarea jetpack-field">
		<label class="jetpack-field-label">Message</label>
		<textarea class="jetpack-field-input jetpack-field-textarea__input" placeholder="Message" rows="5" disabled title="Contact form unavailable – please email us directly"></textarea>
	</div>
	<!-- /wp:group -->

	<!-- wp:group {"className":"wp-block-button","layout":{"type":"default"}} -->
	<div class="wp-block-group wp-block-button">
		<button type="button" class="wp-block-button__link wp-element-button" disabled title="Contact form unavailable – please email us directly">Contact us</button>
	</div>
	<!-- /wp:group -->

	<!-- wp:paragraph {"className":"jetpack-placeholder-notice"} -->
	<p class="jetpack-placeholder-notice" style="font-size: 0.85em; opacity: 0.7; margin-top: 1em; text-align: center;">
		Form temporarily unavailable. Please email <a href="mailto:information@untouchedcocktails.com">information@untouchedcocktails.com</a>
	</p>
	<!-- /wp:paragraph -->

</div>
<!-- /wp:group -->
<?php endif; ?>

