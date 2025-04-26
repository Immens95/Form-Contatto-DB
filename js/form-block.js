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
 
registerBlockType('fsdb/form', {
  title: 'Form Contatto DB',
  icon: 'email',
  category: 'widgets',
  description: 'Un blocco per inserire un form semplice e salvarlo nel database.',
  attributes: {
      nomeLabel: { type: 'string', default: 'Nome' },
      emailLabel: { type: 'string', default: 'Email' },
      messaggioLabel: { type: 'string', default: 'Messaggio' },
      telefonoLabel: { type: 'string', default: 'Telefono' },
      tipoLabel: { type: 'string', default: 'Tipo di richiesta' },
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

      const updateAttribute = (attribute, value) => {
        setAttributes({ [attribute]: value });
      };

      const columnClass = attributes.columns === '2' ? 'form-columns-2' : 'form-columns-1';
      const inputSizeClass = 'input-size-' + attributes.inputSize;

      return el(
        'div',
        blockProps,
        el(InspectorControls, {},
          el(PanelBody, { title: 'Impostazioni Form' },
            el(SelectControl, {
              label: 'Numero colonne',
              value: attributes.columns,
              options: [
                { label: '1 colonna', value: '1' },
                { label: '2 colonne', value: '2' }
              ],
              onChange: (value) => updateAttribute('columns', value)
            }),
            el(SelectControl, {
              label: 'Dimensione input',
              value: attributes.inputSize,
              options: [
                { label: 'Piccolo', value: 'small' },
                { label: 'Medio', value: 'medium' },
                { label: 'Grande', value: 'large' }
              ],
              onChange: (value) => updateAttribute('inputSize', value)
            }),
            el(SelectControl, {
              label: 'Allineamento testo',
              value: attributes.textAlign,
              options: [
                { label: 'Sinistra', value: 'left' },
                { label: 'Centro', value: 'center' },
                { label: 'Destra', value: 'right' }
              ],
              onChange: (value) => updateAttribute('textAlign', value)
            }),
            el(TextControl, {
              label: 'Testo pulsante',
              value: attributes.buttonText,
              onChange: (value) => updateAttribute('buttonText', value)
            })
          )
        ),
        el('form', { className: 'fsdb-form ' +columnClass + ' ' + inputSizeClass },
          el('p', {},
            el(RichText, {
              tagName: 'label',
              value: attributes.nomeLabel,
              onChange: (value) => updateAttribute('nomeLabel', value),
              placeholder: 'Nome'
            }),
            el('br'),
            el('input', { type: 'text', name: 'nome' })
          ),
          el('p', {},
            el(RichText, {
              tagName: 'label',
              value: attributes.emailLabel,
              onChange: (value) => updateAttribute('emailLabel', value),
              placeholder: 'Email'
            }),
            el('br'),
            el('input', { type: 'email', name: 'email' })
          ),
          el('p', {},
            el(RichText, {
              tagName: 'label',
              value: attributes.telefonoLabel,
              onChange: (value) => updateAttribute('telefonoLabel', value),
              placeholder: 'Telefono'
            }),
            el('br'),
            el('input', { type: 'tel', name: 'tel' })
          ),
          el('p', {},
            el(RichText, {
              tagName: 'label',
              value: attributes.tipoLabel,
              onChange: (value) => updateAttribute('tipoLabel', value),
              placeholder: 'Tipo di richiesta'
            }),
            el('br'),
            el('select', { name: 'info' },
              el('option', { value: '' }, 'Seleziona...'),
              el('option', { value: 'info' }, 'Informazioni'),
              el('option', { value: 'preventivo' }, 'Preventivo'),
              el('option', { value: 'supporto' }, 'Supporto')
            )
          ),
          el('p', { className: 'fsdb-message' },
            el(RichText, {
              tagName: 'label',
              value: attributes.messaggioLabel,
              onChange: (value) => updateAttribute('messaggioLabel', value),
              placeholder: 'Messaggio'
            }),
            el('br'),
            el('textarea', { name: 'messaggio' })
          ),
          el('p', { className: 'fsdb-button' },
            el('button', { type: 'submit', onClick: 'event.preventDefault()', Style: 'pointer-events:none;' }, attributes.buttonText)
          )
        )
      );
    },
    save: function (props) {
      const { attributes } = props;
      const blockProps = blockEditor.useBlockProps.save({ style: { textAlign: attributes.textAlign } });

      const columnClass = attributes.columns === '2' ? 'form-columns-2' : 'form-columns-1';
      const inputSizeClass = 'input-size-' + attributes.inputSize;
      
      var buttStyle ='';
      if (attributes.textAlign == 'left') {
        buttStyle = 'margin-left: 0px;';
      }
      if (attributes.textAlign == 'center') {
        buttStyle = 'margin-left: 48%;';
      }
      if (attributes.textAlign == 'right') {
        buttStyle = 'margin-left: auto;';
      }

      return el(
        'div',
        blockProps,
        el('form', { className: 'fsdb-form ' + columnClass + ' ' + inputSizeClass, id: 'fsdb-form' },
          el('p', {},
            el('label', {}, attributes.nomeLabel),
            el('br'),
            el('input', { type: 'text', name: 'nome' })
          ),
          el('p', {},
            el('label', {}, attributes.emailLabel),
            el('br'),
            el('input', { type: 'email', name: 'email' })
          ),
          el('p', {},
            el('label', {}, attributes.telefonoLabel),
            el('br'),
            el('input', { type: 'tel', name: 'tel' })
          ),
          el('p', {},
            el('label', {}, attributes.tipoLabel),
            el('br'),
            el('select', { name: 'info' },
              el('option', { value: '' }, 'Seleziona...'),
              el('option', { value: 'info' }, 'Informazioni'),
              el('option', { value: 'preventivo' }, 'Preventivo'),
              el('option', { value: 'supporto' }, 'Supporto')
            )
          ),
          el('p', { className: 'fsdb-message' },
            el('label', {}, attributes.messaggioLabel),
            el('br'),
            el('textarea', { name: 'messaggio' })
          ),
          el('p', { className: 'fsdb-button', Style: buttStyle },
            el('button', { type: 'submit' }, attributes.buttonText)
          ),
          el('div', { id: 'fsdb-response' })
        )
      );
    }
});







