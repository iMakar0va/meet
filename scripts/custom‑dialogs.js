(function () {

  const create = html => {
    const ov = document.createElement('div');
    ov.className = 'custom-overlay';
    ov.innerHTML = html;
    document.body.appendChild(ov);
    return ov;
  };

  window.alert = msg => new Promise(res => {
    const o = create(`
<div class="custom-dialog">
  <h3>${msg}</h3>
  <button class="cbtn c-ok">ОК</button>
</div>`);
    o.querySelector('.c-ok').onclick = () => {
      o.remove();
      res();
    }
  });


  window.confirm = msg => new Promise(res => {
    const o = create(`
  <div class="custom-dialog">
    <h3>${msg}</h3>
    <button class="cbtn c-ok">ОК</button>
    <button class="cbtn c-cancel">Отмена</button>
  </div>`);

    o.querySelector('.c-ok').onclick = () => {
      o.remove();
      res(true);  // Возвращаем true, когда нажали ОК
    }
    o.querySelector('.c-cancel').onclick = () => {
      o.remove();
      res(false); // Возвращаем false, когда нажали Отмена
    }
  });


  window.prompt = (msg = '', defVal = '') => new Promise(res => {
    const o = create(`
<div class="custom-dialog">
  <h3>${msg}</h3>
  <input type="text" value="${defVal.replace(/" /g, '&quot;')}" />
  <button class="cbtn c-ok">ОК</button>
  <button class="cbtn c-cancel">Отмена</button>
</div>`);
    const inp = o.querySelector('input');
    inp.select();
    o.querySelector('.c-ok').onclick = () => {
      let v = inp.value;
      o.remove();
      res(v);
    }
    o.querySelector('.c-cancel').onclick = () => {
      o.remove();
      res(null);
    }
  });

})();