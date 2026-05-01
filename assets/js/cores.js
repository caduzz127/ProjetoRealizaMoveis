document.addEventListener('DOMContentLoaded', () => {
    const checkbox = document.getElementById('tem-cores');
    const areaCores = document.getElementById('area-cores');
    const lista = document.getElementById('lista-cores');

    let contadorCores = 0;

    checkbox.addEventListener('change', () => {
        if (checkbox.checked) {
            areaCores.style.display = 'block';
        } else {
            areaCores.style.display = 'none';
            console.log('Desmarcou - limpando cores');
            lista.innerHTML = '';
            contadorCores = 0;
        }
    });

    window.adicionarCor = function () {
        if (!checkbox.checked) return;

        const index = contadorCores++;

        const div = document.createElement('div');
        div.classList.add('cor-bloco');

        const cor = produtoEdicao ? produtoEdicao.cor : '';
        const required = produtoEdicao ? '' : 'required';

        div.innerHTML = `
            <div class="section-divider"></div>

            <div class="form-group">
                <label>Cor Secundária:</label>
                <div class="form-group-inputs">
                    <input type="text"
                           name="cores[${index}][cor]"
                           value="${cor}"
                           style="flex: 1;">
                    <input type="color"
                           name="cores[${index}][cor-hex]"
                           style="width: 50px; height: 30px; padding: 0; border-radius: 10px; margin-top: 10px;">
                </div>
            </div>
            <br><br>

            <div class="form-group">
                <label>
                    Upload de Imagem (Selecione Apenas Uma)
                    ${produtoEdicao ? '(deixe em branco para manter a existente)' : '*'}
                </label>
                <input type="file"
                       name="cores[${index}][imagem]"
                       accept="image/*"
                       ${required}>
                <small style="color: #666;">Selecione apenas uma imagem</small>
            </div>

            <div class="form-group">
                <label>Upload de Imagens (As Demais Imagens desta Cor)</label>
                <input type="file"
                       name="cores[${index}][imagens_secundarias][]"
                       multiple
                       accept="image/*">
                <small style="color: #666;">Você pode selecionar várias imagens</small>
            </div>
        `;

        lista.appendChild(div);
        // adicionar preview handlers
        const inputImg = div.querySelector(`input[name="cores[${index}][imagem]"]`);
        const inputImgsSec = div.querySelector(`input[name="cores[${index}][imagens_secundarias][]"]`);
        const previewBox = document.createElement('div');
        previewBox.style.marginTop = '8px';
        div.appendChild(previewBox);

        if (inputImg) {
            inputImg.addEventListener('change', function(){
                const file = this.files[0];
                if (!file) return;
                const reader = new FileReader();
                reader.onload = function(e){
                    previewBox.innerHTML = '<div>Imagem selecionada:</div><img src="'+e.target.result+'" style="max-width:150px; margin-top:8px; border-radius:6px;">';
                };
                reader.readAsDataURL(file);
            });
        }

        if (inputImgsSec) {
            inputImgsSec.addEventListener('change', function(){
                previewBox.innerHTML = '';
                Array.from(this.files).forEach(file => {
                    const reader = new FileReader();
                    reader.onload = function(e){
                        const divc = document.createElement('div');
                        divc.style.textAlign = 'center';
                        divc.style.display = 'inline-block';
                        divc.style.marginRight = '8px';
                        divc.innerHTML = '<img src="'+e.target.result+'" style="max-width:120px; border-radius:6px; display:block;">';
                        previewBox.appendChild(divc);
                    };
                    reader.readAsDataURL(file);
                });
            });
        }
    };
});