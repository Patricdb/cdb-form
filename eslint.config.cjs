module.exports = [
  {
    languageOptions: {
      ecmaVersion: 2021,
      globals: {
        window: 'readonly',
        document: 'readonly',
        jQuery: 'readonly',
        $: 'readonly',
        cdb_form_ajax: 'readonly',
        cdbMsgs_i18n: 'readonly',
        cdbMsgs: 'readonly',
        cdbMensajes: 'readonly',
        Awesomplete: 'readonly'
      }
    },
    rules: {
      'no-unused-vars': 'off',
      'no-undef': 'off'
    }
  }
];
