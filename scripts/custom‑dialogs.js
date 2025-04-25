(function () {
  // Создание контейнера, если его нет
  if (!document.getElementById("dialog-overlay")) {
    const overlay = document.createElement("div");
    overlay.id = "dialog-overlay";
    overlay.style = `
      position: fixed;
      top: 0; left: 0; right: 0; bottom: 0;
      background: rgba(0, 0, 0, 0.5);
      display: none;
      justify-content: center;
      align-items: center;
      z-index: 9999;
    `;
    document.body.appendChild(overlay);
  }

  function showDialog(contentHTML, onReady) {
    const overlay = document.getElementById("dialog-overlay");
    overlay.innerHTML = `
      <div style="
        background: #fff;
        padding: 24px;
        max-width: 400px;
        border-radius: 12px;
        box-shadow: 0 8px 20px rgba(0,0,0,0.2);
        font-family: sans-serif;
        width: 90%;
        position: relative;
      ">
        ${contentHTML}
      </div>
    `;
    overlay.style.display = "flex";
    if (onReady) onReady(overlay);
  }

  function closeDialog() {
    document.getElementById("dialog-overlay").style.display = "none";
  }

  const buttonStyle = `
    padding: 10px 18px;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    cursor: pointer;
    transition: background-color 0.2s ease;
  `;

  const primaryStyle = `
    ${buttonStyle}
    background-color: #f5c85f;
    color: #000;
  `;

  const secondaryStyle = `
    ${buttonStyle}
    background-color: #ddd;
    color: #333;
  `;

  const buttonWrapperStyle = `
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 20px;
  `;

  // Custom alert
  window.customAlert = function (message, onClose) {
    showDialog(`
      <p style="margin-bottom: 10px;">${message}</p>
      <div style="${buttonWrapperStyle}">
        <button id="alert-ok" style="${primaryStyle}">OK</button>
      </div>
    `, function () {
      document.getElementById("alert-ok").onclick = function () {
        closeDialog();
        if (onClose) onClose();
      };
    });
  };

  // Custom confirm
  window.customConfirm = function (message, onConfirm) {
    showDialog(`
      <p style="margin-bottom: 10px;">${message}</p>
      <div style="${buttonWrapperStyle}">
        <button id="confirm-no" style="${secondaryStyle}">Нет</button>
        <button id="confirm-yes" style="${primaryStyle}">Да</button>
      </div>
    `, function () {
      document.getElementById("confirm-yes").onclick = function () {
        closeDialog();
        if (onConfirm) onConfirm(true);
      };
      document.getElementById("confirm-no").onclick = function () {
        closeDialog();
        if (onConfirm) onConfirm(false);
      };
    });
  };

  // Custom prompt
  window.customPrompt = function (message, onResult) {
    showDialog(`
      <p style="margin-bottom: 10px;">${message}</p>
      <input type="text" id="prompt-input" style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #ccc; color: black;" />
      <div style="${buttonWrapperStyle}">
        <button id="prompt-cancel" style="${secondaryStyle}">Отмена</button>
        <button id="prompt-ok" style="${primaryStyle}">OK</button>
      </div>
    `, function () {
      const input = document.getElementById("prompt-input");
      input.focus();

      document.getElementById("prompt-ok").onclick = function () {
        closeDialog();
        if (onResult) onResult(input.value);
      };

      document.getElementById("prompt-cancel").onclick = function () {
        closeDialog();
        if (onResult) onResult(null);
      };
    });
  };
})();
