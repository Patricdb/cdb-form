export default [
  {
    files: ["assets/js/**/*.js"],
    languageOptions: {
      ecmaVersion: 2019,
      sourceType: "script",
      globals: {
        jQuery: "readonly",
        $: "readonly",
        cdbMsgs: "readonly",
        cdbMsgs_i18n: "readonly",
        cdb_form_ajax: "readonly",
        Awesomplete: "readonly",
        cdbMensajes: "readonly",
        window: "readonly",
        document: "readonly",
        location: "readonly",
        setTimeout: "readonly"
      }
    },
    rules: {
      "no-undef": "error",
      "no-unused-vars": ["warn", { "args": "none" }]
    }
  }
];
