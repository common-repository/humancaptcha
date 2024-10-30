<?php
$message = '';
$message2 = '';
if (isset($_POST['updateqanda'])){
	$question = esc_attr(trim($_POST['question']));
	$answer = esc_attr(trim($_POST['answer']));
	$ref = abs(intval($_POST['ref'])); // it's a positive integer
	if ($question && $answer && $ref){
		$message = __('Question and/or answer updated.', 'humancaptcha');
		$this->obr_update_qanda($ref, $question, $answer);
	} else {
		$message = __('Cannot update the question and/or answer.', 'humancaptcha');
	}
}	
if (isset($_POST['deleteqanda'])){
	$ref = abs(intval($_POST['ref'])); // it's a positive integer
	if ($ref){
		$message = __('Selected question and answer deleted.', 'humancaptcha');
		$this->obr_delete_qanda($ref);
	} else {
		$message = __('Cannot delete the selected question and answer.', 'humancaptcha');
	}
}	
if (isset($_POST['addqanda'])){
	$question = esc_attr(trim($_POST['question']));
	$answer = esc_attr(trim($_POST['answer']));
	if ($question && $answer){
		$message = __('New question and answer added.', 'humancaptcha');
		$this->obr_add_qanda($question, $answer);
	} else {
		$message = __('Cannot add the new question and answer.', 'humancaptcha');
	}
}
if (isset($_POST['togglesetting'])){
	$setting = esc_attr(trim($_POST['setting']));
	$value = intval($_POST['value']);
	if ($setting && (($value == 0) || ($value == 1))){
		$message2 = __('Setting updated.', 'humancaptcha');
		//toggle $value
		$value = 1 - $value;
		$this->obr_update_admin_settings($setting, $value);
	} else {
		$message2 = __('Setting could not be updated.', 'humancaptcha');
	}
}
?>
<div class="wrap">
	<h2><?php _e('HumanCaptcha - Settings', 'humancaptcha'); ?></h2>
	<br />
	<h3><?php _e('HumanCaptcha Usage', 'humancaptcha'); ?></h3>
	<p><?php _e('You can decide whether or not HumanCaptcha is applied to comment, registration and/or login forms.  Just toggle the settings below.', 'humancaptcha'); ?></p>
	<?php
	echo $this->obr_admin_settings();
	?>
	<br />
	<h3><?php _e('HumanCaptcha Questions and Answers', 'humancaptcha'); ?></h3>
	<p><?php _e('You can update your questions and answers here.', 'humancaptcha'); ?></p>
	<?php
	if (isset($_POST['addqanda']) && (!$question) && (!$answer)){
		echo $this->obr_qanda_settings($message, $question, $answer);
	} else {
		echo $this->obr_qanda_settings($message);
	}
	?>
	<br />
	<br />
</div>