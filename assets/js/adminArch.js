document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('archivo');
    const fileLabel = document.querySelector('label[for="archivo"]');
    const botonSubida = document.getElementById('subir');
    const botonexpandir = document.getElementById('botonexpandir');
    const camposexpandibles = document.getElementById('camposExpandibles');
    const buscarDocs = document.getElementById('buscarDocs');
    const tableBody = document.getElementById('cuerpoTabla');


    if (userRole === 1) {
        botonSubida.style.display = 'none';

        if (fileInput && fileLabel) {
            fileInput.addEventListener('change', function() {
                if (fileInput.files.length > 0) {
                    fileLabel.textContent = fileInput.files[0].name;
                    botonSubida.style.display = 'block';
                } else {
                    fileLabel.textContent = 'AÑADIR ARCHIVO';
                    botonSubida.style.display = 'none';
                }
            });
        }
    }

    if (botonexpandir && camposexpandibles) {
        botonexpandir.addEventListener('click', () => {
            if (camposexpandibles.classList.contains('hidden')) {
                camposexpandibles.classList.remove('hidden');
                botonexpandir.textContent = 'Cerrar';
            } else {
                camposexpandibles.classList.add('hidden');
                botonexpandir.textContent = 'Añadir archivo';
            }
        });
    }

    if (buscarDocs && tableBody) {
        buscarDocs.addEventListener('input', function() {
            const consulta = buscarDocs.value;
            fetch(`buscadorAJAX.php?query=${consulta}`)
                .then(response => response.json())
                .then(data => {
                    tableBody.innerHTML = '';
                    if (data.length > 0) {
                        data.forEach(row => {
                            const tr = document.createElement('tr');
                            tr.id = `row-${row.id}`;
                            tr.innerHTML = `
                                <td class="check"><input type="checkbox" name="ids[]" value="<?= $archivo['id'] ?>"></td>
                                <td class="botonesfile">
                                    <a class="verbtn" href="verArchivo.php?id=${row.id}"><i class="material-icons">import_contacts</i></a>
                                    ${userRole === 1 ? `
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="id" value="${row.id}">
                                        <button class="verelbtn" type="submit" name="eliminar_individual"><i class="material-icons">delete_sweep</i></button>
                                    </form>` : ''}
                                </td>
                                <td>${row.nomArchivo}</td>
                            `;
                            tableBody.appendChild(tr);
                        });
                    } else {
                        const tr = document.createElement('tr');
                        tr.innerHTML = '<td colspan="3">No se encontraron resultados</td>';
                        tableBody.appendChild(tr);
                    }
                })
                .catch(error => console.error('Error:', error));
        });
    }
});