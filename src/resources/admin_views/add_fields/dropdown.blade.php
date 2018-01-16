<tr class="top aligned field-single-line-text">
	<th class="collapsing">
		{{ $field_title }}
		@if (array_key_exists('required', $field) && $field['required'] === true)
			<i class="orange small asterisk icon" 
				title="{{ trans('common::admin.add.errors.validation.required') }}"></i>
		@endif
	</th>
	<td>
		<div class="field">
			<?php
			$multiple = array_key_exists('multiple', $field) && $field['multiple'] === true;
			
			$field_name = 'field[' . $id_language . '][' . $field['name'] . ']';
			if ($multiple)
			{
				$field_name .= '[]';
			}
			?>
			<select class="ui search dropdown" name="{{ $field_name }}"
				{!! $multiple ? 'multiple=""' : '' !!}>
				@if ((!array_key_exists('required', $field) || $field['required'] === false) && !$multiple)
					<option value="">{{ trans('common::admin.add.dropdown.empty-value') }}</option>
				@endif
				@foreach ($field['values'] as $key=>$title)
					<?php
					$selected = false;
					if (array_key_exists('value', $field))
					{
						if (is_array($field['value'])) //multiple values
						{
							$selected = in_array($key, $field['value']);
						}
						else
						{
							$selected = $key == $field['value'];
						}
					}
					?>
					<option value="{{ $key }}" {{ $selected ? 'selected' : '' }}>
						{{ $title }}
					</option>
				@endforeach
			</select>
		</div>
	</td>
</tr>
