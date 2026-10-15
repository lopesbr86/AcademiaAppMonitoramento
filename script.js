function iniciarApp() {
    const formBusca = document.getElementById("form-busca");
    const resultadosContainer = document.getElementById("resultados-container");

    if (formBusca) {
        formBusca.addEventListener("submit", function (e) {
            e.preventDefault();

            const cidade = document.getElementById("cidade").value;
            const academia = document.getElementById("academia").value;

            if (resultadosContainer) {
                resultadosContainer.innerHTML = `<p style="text-align: center; color: #718096; padding: 20px; font-size: 0.9rem;">A pesquisar academias em ${cidade}...</p>`;
            }

            fetch(`buscar_academia.php?cidade=${encodeURIComponent(cidade)}&academia=${encodeURIComponent(academia)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.erro) {
                        if (resultadosContainer) {
                            resultadosContainer.innerHTML = `<p style="text-align: center; color: #e53e3e; padding: 20px; font-size: 0.9rem;">${data.erro}</p>`;
                        }
                    } else {
                        renderizarResultados(data);
                    }
                })
                .catch(err => {
                    console.error("Erro na busca:", err);
                    if (resultadosContainer) {
                        resultadosContainer.innerHTML = `<p style="text-align: center; color: #e53e3e; padding: 20px; font-size: 0.9rem;">Erro ao conectar com o servidor.</p>`;
                    }
                });
        });
    }
}

// Executa de forma segura quer o DOM já tenha carregado ou não
if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", iniciarApp);
} else {
    iniciarApp();
}

// Função para renderizar os cards de academias na tela
function renderizarResultados(listaAcademias) {
    const resultadosContainer = document.getElementById("resultados-container");
    if (!resultadosContainer) return;

    resultadosContainer.innerHTML = "";

    if (!listaAcademias || listaAcademias.length === 0) {
        resultadosContainer.innerHTML = `<p style="text-align: center; color: #718096; padding: 20px; font-size: 0.9rem;">Nenhuma academia encontrada.</p>`;
        return;
    }

    listaAcademias.forEach(acab => {
        const card = document.createElement("div");
        card.className = "academia-card";

        const iconeEstrela = acab.ehFavorita ? '⭐' : '☆';

        card.innerHTML = `
            <div class="info" style="flex: 1;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <h3 style="margin: 0;"></h3>
                    <button type="button" class="btn-favorito" id="fav-${acab.id}" style="background: none; border: none; font-size: 1.4rem; cursor: pointer;" title="Definir como favorita">
                        ${iconeEstrela}
                    </button>
                </div>
                <p style="margin: 5px 0;"></p>
                <span id="status-${acab.id}" class="status-badge cinza">A carregar lotação...</span>
            </div>
            <div class="acoes-voto" style="margin-top: 10px;">
                <button type="button" class="btn-avaliar" style="width: 100%; padding: 8px; border-radius: 6px; background: #2563eb; color: white; border: none; cursor: pointer; font-weight: bold;">Avaliar</button>
            </div>
        `;

        card.querySelector("h3").textContent = acab.nome;
        card.querySelector("p").textContent = acab.endereco || acab.cidade;
        
        card.querySelector(".btn-avaliar").addEventListener("click", () => abrirModalVoto(acab.id, acab.nome));
        card.querySelector(`#fav-${acab.id}`).addEventListener("click", () => alternarFavorito(acab.id));

        resultadosContainer.appendChild(card);

        verificarLotacao(acab.id);
    });
}

function alternarFavorito(idAcademia) {
    fetch(`favoritar_academia.php?id=${idAcademia}`)
        .then(response => response.json())
        .then(data => {
            if (data.sucesso) {
                const btnFav = document.getElementById(`fav-${idAcademia}`);
                if (btnFav) {
                    btnFav.textContent = data.favorito ? '⭐' : '☆';
                }
            } else {
                alert(data.erro || "Erro ao atualizar favoritos.");
            }
        })
        .catch(err => console.error("Erro ao favoritar:", err));
}

function verificarLotacao(idAcademia) {
    fetch(`buscar_lotacao.php?id_academia=${idAcademia}`)
        .then(response => response.json())
        .then(data => {
            const badge = document.getElementById(`status-${idAcademia}`);
            if (badge && !data.erro) {
                badge.innerText = data.status;
                badge.className = `status-badge ${data.cor}`;
            }
        })
        .catch(err => console.error("Erro ao buscar lotação:", err));
}

function abrirModalVoto(idAcademia, nomeAcademia) {
    const modalAntigo = document.getElementById('modal-avaliacao');
    if (modalAntigo) modalAntigo.remove();

    const modal = document.createElement('div');
    modal.id = 'modal-avaliacao';
    modal.className = 'modal-overlay';

    modal.innerHTML = `
        <div class="modal-card">
            <h3>Avaliar Lotação</h3>
            <p>Como está a lotação da <strong class="nome-academia"></strong> agora?</p>

            <div class="opcoes-voto">
                <button type="button" class="btn-opcao verde" onclick="enviarVotoDireto(${idAcademia}, 1)">
                    <span class="bolinha">🟢</span> Vazia / Tranquila
                </button>
                <button type="button" class="btn-opcao amarelo" onclick="enviarVotoDireto(${idAcademia}, 2)">
                    <span class="bolinha">🟡</span> Moderada
                </button>
                <button type="button" class="btn-opcao vermelho" onclick="enviarVotoDireto(${idAcademia}, 3)">
                    <span class="bolinha">🔴</span> Lotada
                </button>
            </div>

            <button type="button" class="btn-fechar-modal" onclick="fecharModalVoto()">Cancelar</button>
        </div>
    `;

    modal.querySelector('.nome-academia').textContent = nomeAcademia;
    document.body.appendChild(modal);
}

function fecharModalVoto() {
    const modal = document.getElementById('modal-avaliacao');
    if (modal) modal.remove();
}

function enviarVotoDireto(idAcademia, nivelLotacao) {
    const formData = new FormData();
    formData.append('idAcademia', idAcademia);
    formData.append('nivelLotacao', nivelLotacao);

    fetch('votar_academia.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            fecharModalVoto();
            if (data.sucesso) {
                alert("Voto registrado com sucesso! Obrigado por ajudar a comunidade GymUp.");
                verificarLotacao(idAcademia);
            } else {
                alert(data.erro || "Erro ao registrar o voto.");
            }
        })
        .catch(err => {
            fecharModalVoto();
            alert("Erro ao registrar o voto.");
            console.error(err);
        });
}