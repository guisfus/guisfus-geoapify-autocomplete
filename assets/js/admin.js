/**
 * Admin settings helpers for dynamic field mappings.
 */
(function () {
  const cfg = window.GuisFusGeoapifyAutocompleteAdmin;
  if (!cfg || !cfg.optionKey) return;

  const wrap = document.getElementById("gaa-forms");
  const addBtn = document.getElementById("gaa-add");
  if (!wrap || !addBtn) return;

  const i18n = cfg.i18n || {};
  const fields = [
    ["address", i18n.address || "Address field ID"],
    ["city", i18n.city || "City field ID"],
    ["state", i18n.state || "State field ID"],
    ["zip", i18n.zip || "Postal code field ID"],
  ];

  function nextIndex() {
    return wrap.querySelectorAll(".gaa-form-row").length;
  }

  function appendText(parent, text) {
    parent.appendChild(document.createTextNode(text));
  }

  function createField(base, key, labelText) {
    const p = document.createElement("p");
    const label = document.createElement("label");
    const strong = document.createElement("strong");
    const input = document.createElement("input");

    appendText(strong, labelText);
    label.appendChild(strong);

    input.className = "regular-text";
    input.type = "text";
    input.name = base + "[" + key + "]";
    input.value = "";

    p.appendChild(label);
    p.appendChild(document.createElement("br"));
    p.appendChild(input);

    return p;
  }

  function makeRow(index) {
    const base = cfg.optionKey + "[forms][" + index + "]";
    const row = document.createElement("div");
    const title = document.createElement("p");
    const titleStrong = document.createElement("strong");
    const remove = document.createElement("button");

    row.className = "gaa-form-row";

    appendText(titleStrong, (i18n.formTitle || "Form #") + (index + 1));
    title.appendChild(titleStrong);
    row.appendChild(title);

    fields.forEach(([key, label]) => {
      row.appendChild(createField(base, key, label));
    });

    remove.type = "button";
    remove.className = "button gaa-remove";
    appendText(remove, i18n.remove || "Remove");
    row.appendChild(remove);

    return row;
  }

  addBtn.addEventListener("click", function () {
    wrap.appendChild(makeRow(nextIndex()));
  });

  wrap.addEventListener("click", function (event) {
    const btn = event.target.closest(".gaa-remove");
    if (!btn) return;

    const row = btn.closest(".gaa-form-row");
    if (row) row.remove();
  });
})();
