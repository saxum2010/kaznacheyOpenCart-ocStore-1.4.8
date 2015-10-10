<form action="<?php echo $action; ?>" accept-charset="utf-8" method="post" id="payment" name="kaznachey_payment">
	<input type="hidden" name="order_id" value="<?php echo $order_id ?>">
	<? echo $cc_type; ?>
	<? echo $cc_agreed; ?>
</form>

<div class="buttons">
	<div class="right"><a onclick="check_cc();" class="button"><span><?php echo $button_confirm; ?></span></a></div>
</div>

<script type="text/javascript"><!--

(function(){ 
var cc_a = $('#cc_agreed');
	 cc_a.click(function(){
		if(cc_a.is(':checked')){	
			$('#payment').find('.error').text('');
		}else{
			cc_a.after('<span class="error">Примите условие!</span>');
		}
	 });
})(); 

function check_cc()
{

$('[name="kaznachey_payment"]').submit();

/* 	$.ajax({
		url: 'index.php?route=payment/kaznachey/validate',
		type: 'post',
		data: $('#payment-address input[type=\'text\'], #payment-address input[type=\'checkbox\']:checked, #cc_type, #cc_agreed, #payment-address select'),
		dataType: 'json',
		success: function(json) {
			
			 if(json['error'])
			{
				if (json['error']['email']) {
					$('#payment-address input[name=\'email\'] + br').after('<span class="error">' + json['error']['email'] + '</span>');
					
					if(json['customer']['customer_id'])
					{
						$('#payment-address #payment-address-existing').before('<span class="error">' + json['error']['email'] + '</span>');
					}
				}
				
				if (json['error']['telephone']) {
					$('#payment-address input[name=\'telephone\'] + br').after('<span class="error">' + json['error']['telephone'] + '</span>');
					
					if(json['customer']['customer_id'])
					{
						$('#payment-address #payment-address-existing').before('<span class="error">' + json['error']['telephone'] + '</span><a href="/index.php?route=account/edit">Edit</a><br>');
					}
				}				
				
					$('#payment-address .checkout-content').slideDown('slow');
					
					$('#confirm .checkout-content').slideUp('slow');
					
	
			
			}else{
				$('#payment').submit();
			}
 
		},
	
	});	 */
};


//--></script> 