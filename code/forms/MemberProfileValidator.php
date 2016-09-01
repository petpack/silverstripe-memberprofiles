<?php
/**
 * This validator provides the unique and required functionality for {@link MemberProfileField}s.
 *
 * @package silverstripe-memberprofiles
 */
class MemberProfileValidator extends RequiredFields {

	protected $fields, $member, $unique = array();

	/**
	 * @param MemberProfileField[] $fields
	 * @param Member $member
	 */
	public function __construct($fields, $member = null) {
		parent::__construct(
			Array("Email")
		);

		$this->fields = $fields;
		$this->member = $member;

		foreach($this->fields as $field) {
			if($field->Required) $this->addRequiredField($field->MemberField);
			if($field->Unique)   $this->unique[] = $field->MemberField;
		}
	}

	/**
	 * JavaScript validation is disabled on profile forms.
	 */
	public function javascript() {
		return null;
	}

	public function php($data) {
		
		$valid = parent::php($data);
		if (!$valid) return $valid;
		
		$member   = Member::currentUser();
		
		foreach($this->unique as $field) {
			$other = DataObject::get_one (
				'Member',
				sprintf('"%s" = \'%s\'', Convert::raw2sql($field), Convert::raw2sql($data[$field]))
			);

			if ($other && (!$this->member || !$this->member->exists() || $other->ID != $this->member->ID)) {
				$fieldInstance = $this->form->dataFieldByName($field);

				if($fieldInstance->getCustomValidationMessage()) {
					$message = $fieldInstance->getCustomValidationMessage();
				} else {
					$message = sprintf (
						_t('MemberProfiles.MEMBERWITHSAME', 'There is already a member with the same %s.'),
						$field
					);
				}

				$valid = false;
				$this->validationError($field, $message, 'required');
			}
		}

		return $valid;
	}

}