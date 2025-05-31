  const { registerBlockType } = wp.blocks;
  const blocks = window.wp.blocks;
  const blockEditor = window.wp.blockEditor;
  const element = window.wp.element;
  const components = window.wp.components;

  const el = element.createElement;
  const useBlockProps = blockEditor.useBlockProps;
  const RichText = blockEditor.RichText;
  const InspectorControls = blockEditor.InspectorControls;
  const PanelBody = components.PanelBody;
  const SelectControl = components.SelectControl;
  const TextControl = components.TextControl;
  const TextareaControl = components.TextareaControl;
  const ToggleControl = components.ToggleControl;
  const Button = components.Button;
  const IconButton = components.IconButton; // For up/down/remove buttons
 
registerBlockType('fsdb/form', {
  title: 'Form Contatto DB',
  icon: 'email',
  category: 'widgets',
  description: 'Un blocco per inserire un form semplice e salvarlo nel database.',
  attributes: {
      fields: { type: 'array', default: [] },
      buttonText: { type: 'string', default: 'Invia' },
      columns: { type: 'string', default: '1' },
      inputSize: { type: 'string', default: 'medium' },
      textAlign: { type: 'string', default: 'left' }
    },
    supports: {
      color: {
        background: true,
        text: true,
        link: true,
        gradients: true
      },
      background: {
        backgroundImage: true, // Enable background image control.
        backgroundSize: true // Enable background image + size control.
      },
      spacing: {
        margin: true,
        padding: true
      },
      typography: {
        fontSize: true,
        lineHeight: true,
        fontFamily: true,
        fontWeight: true,
        textAlign: true
      },
      shadow: true, // Enable the box-shadow picker.
      alignWide: true,
      reusable: true,
      className: true,
      position: {
        sticky: true // Enable selecting sticky position.
      },
      border: {
        radius: true,
        width: true,
        color: true,
        style: true
      }
    },
    edit: function (props) {
      const { attributes, setAttributes } = props;
      const blockProps = useBlockProps({ style: { textAlign: attributes.textAlign } });

      const updateGlobalAttribute = (attribute, value) => {
        setAttributes({ [attribute]: value });
      };

      const addNewField = () => {
        const newField = {
          id: 'field_' + new Date().getTime(),
          name: 'new_field_' + new Date().getTime(),
          label: 'New Field',
          type: 'text',
          placeholder: '',
          isRequired: false,
          options: '', // For select type
        };
        setAttributes({ fields: [...attributes.fields, newField] });
      };

      const updateField = (index, fieldData) => {
        const newFields = attributes.fields.map((field, i) => {
          if (i === index) {
            return { ...field, ...fieldData };
          }
          return field;
        });
        setAttributes({ fields: newFields });
      };

      const removeField = (index) => {
        const newFields = attributes.fields.filter((_, i) => i !== index);
        setAttributes({ fields: newFields });
      };

      const moveField = (index, direction) => {
        const newFields = [...attributes.fields];
        const fieldToMove = newFields[index];
        if (direction === 'up' && index > 0) {
          newFields.splice(index, 1);
          newFields.splice(index - 1, 0, fieldToMove);
        } else if (direction === 'down' && index < newFields.length - 1) {
          newFields.splice(index, 1);
          newFields.splice(index + 1, 0, fieldToMove);
        }
        setAttributes({ fields: newFields });
      };


      const columnClass = attributes.columns === '2' ? 'form-columns-2' : 'form-columns-1';
      const inputSizeClass = 'input-size-' + attributes.inputSize;

      return el(
        'div',
        blockProps,
        el(InspectorControls, {},
          el(PanelBody, { title: 'Impostazioni Form', initialOpen: false },
            el(SelectControl, {
              label: 'Numero colonne',
              value: attributes.columns,
              options: [
                { label: '1 colonna', value: '1' },
                { label: '2 colonne', value: '2' }
              ],
              onChange: (value) => updateGlobalAttribute('columns', value)
            }),
            el(SelectControl, {
              label: 'Dimensione input',
              value: attributes.inputSize,
              options: [
                { label: 'Piccolo', value: 'small' },
                { label: 'Medio', value: 'medium' },
                { label: 'Grande', value: 'large' }
              ],
              onChange: (value) => updateGlobalAttribute('inputSize', value)
            }),
            el(SelectControl, {
              label: 'Allineamento testo',
              value: attributes.textAlign,
              options: [
                { label: 'Sinistra', value: 'left' },
                { label: 'Centro', value: 'center' },
                { label: 'Destra', value: 'right' }
              ],
              onChange: (value) => updateGlobalAttribute('textAlign', value)
            }),
            el(TextControl, {
              label: 'Testo pulsante',
              value: attributes.buttonText,
              onChange: (value) => updateGlobalAttribute('buttonText', value)
            })
          ),
          el(PanelBody, { title: 'Form Fields', initialOpen: true },
            attributes.fields.map((field, index) => {
              return el('div', { key: field.id, className: 'fsdb-field-control-group', style:{border:'1px solid #ddd', padding:'10px', marginBottom:'10px'} },
                el(TextControl, {
                  label: 'Label',
                  value: field.label,
                  onChange: (value) => updateField(index, { label: value })
                }),
                el(TextControl, {
                  label: 'Name',
                  value: field.name,
                  onChange: (value) => updateField(index, { name: value })
                }),
                el(SelectControl, {
                  label: 'Type',
                  value: field.type,
                  options: [
                    { label: 'Text', value: 'text' },
                    { label: 'Email', value: 'email' },
                    { label: 'Textarea', value: 'textarea' },
                    { label: 'Select', value: 'select' },
                    { label: 'Checkbox (Singolo)', value: 'checkbox' },
                    { label: 'Radio Buttons', value: 'radio' }
                  ],
                  onChange: (value) => updateField(index, { type: value })
                }),
                (field.type === 'text' || field.type === 'email' || field.type === 'textarea') && el(TextControl, {
                  label: 'Placeholder',
                  value: field.placeholder,
                  onChange: (value) => updateField(index, { placeholder: value })
                }),
                el(ToggleControl, {
                  label: 'Required',
                  checked: field.isRequired,
                  onChange: (value) => updateField(index, { isRequired: value })
                }),
                (field.type === 'select' || field.type === 'radio') && el(TextareaControl, {
                  label: 'Options (comma-separated)',
                  value: field.options,
                  onChange: (value) => updateField(index, { options: value }),
                  help: 'Enter each option separated by a comma. E.g. Option 1,Option 2'
                }),
                el('div', { style: {display: 'flex', justifyContent:'space-between', marginTop:'5px'} },
                  el(IconButton, {
                    icon: 'arrow-up-alt2',
                    label: 'Move Up',
                    onClick: () => moveField(index, 'up'),
                    disabled: index === 0
                  }),
                  el(IconButton, {
                    icon: 'arrow-down-alt2',
                    label: 'Move Down',
                    onClick: () => moveField(index, 'down'),
                    disabled: index === attributes.fields.length - 1
                  }),
                  el(IconButton, {
                    icon: 'trash',
                    label: 'Remove Field',
                    onClick: () => removeField(index),
                    style: { color: 'red' }
                  })
                )
              );
            }),
            el(Button, {
              onClick: addNewField,
              isPrimary: true,
              style: { marginTop: '10px' }
            }, 'Add Field')
          )
        ),
        el('div', { className: 'fsdb-form-editor-preview ' + columnClass + ' ' + inputSizeClass },
          attributes.fields.map((field) => {
            let fieldPreview;
            const fieldProps = {
              key: field.id,
              name: field.name,
              placeholder: field.placeholder,
              disabled: true,
              style: { width: '100%', marginBottom: '5px', padding: '8px', border: '1px solid #ccc', backgroundColor: '#f9f9f9' },
              // For checkbox and radio, the main fieldProps might not be directly applicable to the input itself in the same way
            };

            const fieldLabel = el('label', { style:{display:'block', marginBottom:'3px', fontWeight:'bold'} }, field.label + (field.isRequired ? ' *' : ''));

            if (field.type === 'textarea') {
              fieldPreview = el('textarea', { ...fieldProps, placeholder: field.placeholder });
            } else if (field.type === 'select') {
              const options = field.options ? field.options.split(',').map(opt => opt.trim()) : [];
              fieldPreview = el('select', fieldProps,
                options.map(opt => el('option', { key: opt, value: opt }, opt))
              );
            } else if (field.type === 'checkbox') {
              // Checkbox label is usually to the right. The main field.label acts as the text part of the label.
              // The fieldLabel (legend) is displayed above, this is just the input.
              // For preview, we show the main label above, and then the checkbox.
              // The actual label for a single checkbox is often part of the control.
              fieldPreview = el('label', { style: { display: 'flex', alignItems: 'center' } },
                el('input', { type: 'checkbox', disabled: true, style:{marginRight: '8px'} }),
                // field.label // The main label is already rendered by fieldLabel
                 // If checkbox needs its own distinct label text property, it could be field.checkboxLabel or similar.
                 // For now, the main field.label acts as the descriptive label for the checkbox.
                 // If the intention is that field.label is THE text next to the checkbox, then fieldLabel shouldn't be rendered for checkbox type.
                 // Let's assume field.label is the primary label for the input.
                 // For a single checkbox, the `field.label` is the text next to it.
                 // So, we might not need the `fieldLabel` variable above for this type.
                 // Let's adjust: for checkbox, the field.label is THE label for the input.
              );
              // Re-thinking: The outer loop already prints field.label as a general title.
              // For a single checkbox, this field.label is the text *next* to the checkbox.
              // So the structure should be: <label for preview><input type=checkbox>MAIN_LABEL_TEXT</label>
              // The `fieldLabel` var might be redundant if we style it this way.
              // Let's keep `fieldLabel` as the title, and the checkbox itself will just be an input.
              // The user will see: Title: [ ] . This might be confusing.
              // Correct approach for single checkbox: The field.label IS the text next to the checkbox.
              // So, we will not use the generic `fieldLabel` for `checkbox` type.
              return el('div', { key: field.id, className: 'fsdb-field-preview', style:{marginBottom:'10px'} },
                el('label', { style: { display: 'flex', alignItems: 'center', fontWeight:'normal' } }, // override bold for this label type
                    el('input', { type: 'checkbox', disabled: true, style:{marginRight: '8px'} }),
                    field.label + (field.isRequired ? ' *' : '') // label text next to checkbox
                )
              );

            } else if (field.type === 'radio') {
              const radioOptions = field.options ? field.options.split(',').map(opt => opt.trim()) : [];
              if (radioOptions.length > 0) {
                fieldPreview = radioOptions.map(opt => {
                  return el('label', { key: opt, style:{ marginRight: '15px', display: 'inline-block', fontWeight:'normal' } },
                    el('input', { type: 'radio', name: field.name, value: opt, disabled: true, style:{marginRight: '5px'} }),
                    opt
                  );
                });
              } else {
                fieldPreview = el('em', {}, 'Aggiungi opzioni nel pannello');
              }
            } else { // text, email, etc.
              fieldPreview = el('input', { ...fieldProps, type: field.type, placeholder: field.placeholder });
            }

            // For types other than checkbox, fieldLabel is used as the main title for the field group.
            if (field.type !== 'checkbox') {
                 return el('div', { key: field.id, className: 'fsdb-field-preview', style:{marginBottom:'10px'} },
                    fieldLabel, // This is the main label for the field/group
                    fieldPreview
                );
            }
            // For checkbox, we've already returned its specific structure.
            // This means fieldPreview for checkbox type is not used in this path.
            return fieldPreview; // Should not be reached if checkbox handled above. Fallback.

          }),
          el('div', { className: 'fsdb-button-preview-wrapper', style: { marginTop: '20px' } },
            el('button', { type: 'submit', disabled: true, style: { pointerEvents: 'none' } }, attributes.buttonText)
          )
        )
      );
    },
    save: function (props) {
      const { attributes } = props;
      const blockProps = blockEditor.useBlockProps.save({
        style: { textAlign: attributes.textAlign }
      });

      // columnClass and inputSizeClass are not directly used for wrapper here,
      // as PHP handles the front-end rendering. They are more for editor preview consistency.
      // const columnClass = attributes.columns === '2' ? 'form-columns-2' : 'form-columns-1';
      // const inputSizeClass = 'input-size-' + attributes.inputSize;
      
      let buttonWrapperStyle = {};
      if (attributes.textAlign === 'center') {
        buttonWrapperStyle.textAlign = 'center';
      } else if (attributes.textAlign === 'right') {
        buttonWrapperStyle.textAlign = 'right';
      }

      return el(
        'div',
        blockProps,
        attributes.fields.map((field) => {
          // Use field.id for key and ensure it's part of commonFieldProps if used for id attribute
          const commonFieldProps = { name: field.name, id: field.id };
          let fieldOutput; // This will hold the input/select/textarea element(s)

          // Add isRequired to props if needed by specific elements, e.g. for client-side validation hints
           if (field.isRequired) {
             commonFieldProps.required = true;
           }


          if (field.type === 'textarea') {
            fieldOutput = el('textarea', { ...commonFieldProps, placeholder: field.placeholder });
          } else if (field.type === 'select') {
            const options = field.options ? field.options.split(',').map(opt => opt.trim()) : [];
            fieldOutput = el('select', commonFieldProps,
              el('option', { value: '' }, 'Seleziona...'), // Default empty option
              options.map(opt => el('option', { key: opt, value: opt }, opt))
            );
          } else if (field.type === 'checkbox') {
            // For save, a single checkbox. Value is typically 'true' or can be customized.
            // The label wraps the input for better accessibility.
            return el('div', { key: field.id, className: 'fsdb-field-saved-wrapper fsdb-checkbox-wrapper' },
              el('label', { htmlFor: field.id },
                el('input', { type: 'checkbox', ...commonFieldProps, value: 'true' }), // commonFieldProps includes id
                ' ' + field.label
              )
            );
          } else if (field.type === 'radio') {
            const radioOptions = field.options ? field.options.split(',').map(opt => opt.trim()) : [];
            if (radioOptions.length > 0) {
              // For radio group, the main label acts as a legend.
              // Each option then has its own label.
              return el('div', { key: field.id, className: 'fsdb-field-saved-wrapper fsdb-radio-wrapper' },
                el('fieldset', {}, // Use fieldset for grouping radio buttons
                  el('legend', {}, field.label + (field.isRequired ? ' *' : '')),
                  radioOptions.map((opt, index) => {
                    const radioId = field.id + '-' + index;
                    // For radio, 'name' must be common to the group, 'id' should be unique per option.
                    return el('label', { key: opt, htmlFor: radioId, style:{ marginRight: '15px', display: 'inline-block', fontWeight: 'normal'} },
                      el('input', { type: 'radio', name: field.name, id: radioId, value: opt, required: field.isRequired && index === 0 }),
                      ' ' + opt
                    );
                  })
                )
              );
            } else {
              return null; // No options, render nothing for radio in save if misconfigured
            }
          } else { // text, email, number, etc.
            fieldOutput = el('input', { ...commonFieldProps, type: field.type, placeholder: field.placeholder });
          }

          // Default structure for most fields (not checkbox, not radio group with options)
          // Ensure field.id is unique for htmlFor and input id
          return el('div', { key: field.id, className: 'fsdb-field-saved-wrapper' },
            el('label', { htmlFor: field.id }, field.label + (field.isRequired ? ' *' : '') ),
            fieldOutput
          );
        }),
        el('div', { className: 'fsdb-button-saved-wrapper', style: buttonWrapperStyle },
          el('button', { type: 'submit' }, attributes.buttonText)
        )
      );
    }
});







