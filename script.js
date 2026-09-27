document.addEventListener("DOMContentLoaded", () => {
    const formBusca = document.getElementById("form-busca");
    const resultadosContainer = document.getElementById("resultados-container");

    if (formBusca) {
        formBusca.addEventListener("submit", function (e) {
            e.preventDefault(); // Impede o recarregamento padrão do formulário

            const cidade = document.getElementById("cidade").value;
            const academia = document.getElementById("academia").value;

            // Feedback visual de carregamento
            if (resultadosContainer) {
                resultadosContainer.innerHTML = `<p style="text-align: center; color: #718096; padding: 20px; font-size: 0.9rem;">A pesquisar academias em ${cidade}...</p>`;
            }

            // Faz a requisição AJAX para o ficheiro buscar_academia.php
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
});

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

        card.innerHTML = `
            <div class="info">
                <h3>${acab.nome}</h3>
                <p>${acab.cidade}</p>
                <span id="status-${acab.id}" class="status-badge cinza">A carregar lotação...</span>
            </div>
            <div class="acoes-voto">
                <button type="button" onclick="abrirModalVoto(${acab.id}, '${acab.nome.replace(/'/g, "\\'")}', '${acab.google_place_id}', '${acab.cidade}')" class="btn-avaliar">Avaliar</button>
            </div>
        `;

        resultadosContainer.appendChild(card);

        // Consulta a lotação baseada nos votos recentes dos últimos 90 minutos
        verificarLotacao(acab.id);
    });
}

// Faz a requisição ao buscar_lotacao.php para atualizar o status do card
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

// Abre o Modal Moderno de Avaliação
function abrirModalVoto(idAcademia, nomeAcademia, googlePlaceId, cidade) {
    const modalAntigo = document.getElementById('modal-avaliacao');
    if (modalAntigo) modalAntigo.remove();

    const modal = document.createElement('div');
    modal.id = 'modal-avaliacao';
    modal.className = 'modal-overlay';

    modal.innerHTML = `
        <div class="modal-card">
            <h3>Avaliar Lotação</h3>
            <p>Como está a lotação da <strong>${nomeAcademia}</strong> agora?</p>
            
            <div class="opcoes-voto">
                <button type="button" class="btn-opcao verde" onclick="enviarVotoDireto(${idAcademia}, '${googlePlaceId}', '${nomeAcademia}', '${cidade}', 1)">
                    <span class="bolinha">🟢</span> Vazia / Tranquila
                </button>
                <button type="button" class="btn-opcao amarelo" onclick="enviarVotoDireto(${idAcademia}, '${googlePlaceId}', '${nomeAcademia}', '${cidade}', 2)">
                    <span class="bolinha">🟡</span> Moderada
                </button>
                <button type="button" class="btn-opcao vermelho" onclick="enviarVotoDireto(${idAcademia}, '${googlePlaceId}', '${nomeAcademia}', '${cidade}', 3)">
                    <span class="bolinha">🔴</span> Lotada
                </button>
            </div>

            <button type="button" class="btn-fechar-modal" onclick="fecharModalVoto()">Cancelar</button>
        </div>
    `;

    document.body.appendChild(modal);
}

function fecharModalVoto() {
    const modal = document.getElementById('modal-avaliacao');
    if (modal) modal.remove();
}

// Envia o voto via POST para o votar.php
function enviarVotoDireto(idAcademia, googlePlaceId, nomeAcademia, cidade, nivelLotacao) {
    const formData = new FormData();
    formData.append('google_place_id', googlePlaceId);
    formData.append('nome_academia', nomeAcademia);
    formData.append('cidade', cidade);
    formData.append('nivel_lotacao', nivelLotacao);

    fetch('votar.php', {
        method: 'POST',
        body: formData
    })
        .then(response => {
            fecharModalVoto();
            alert("Voto registrado com sucesso! Obrigado por ajudar a comunidade GymUp.");
            verificarLotacao(idAcademia); // Atualiza o status na tela imediatamente
        })
        .catch(err => {
            alert("Erro ao registrar o voto.");
            console.error(err);
        });
}