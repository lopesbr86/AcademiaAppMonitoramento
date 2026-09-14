// 1. Função para buscar academias por termo, cidade e estado (Nominatim/OpenStreetMap)
async function buscarAcademiasNaRegiao(termoBusca, cidade, estado) {
    const queryCompleta = `${termoBusca}, ${cidade}, ${estado}, Brasil`;
    const url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(queryCompleta)}&limit=5`;

    try {
        const resposta = await fetch(url, {
            headers: {
                'User-Agent': 'MonitoramentoAcademiaApp/1.0'
            }
        });

        const locais = await resposta.json();

        if (locais.length === 0) {
            alert("Nenhuma academia encontrada com esses dados.");
            return [];
        }

        return locais.map(local => ({
            nome: local.display_name,
            lat: parseFloat(local.lat),
            lon: parseFloat(local.lon)
        }));

    } catch (erro) {
        console.error("Erro ao buscar localização:", erro);
        alert("Erro ao conectar com o serviço de mapas.");
        return [];
    }
}

// 2. Função acionada pelo botão do HTML para rodar a busca
async function executarBusca() {
    const cidade = document.getElementById('cidade').value;
    const nomeAcademia = document.getElementById('academia').value;

    if (!cidade || !nomeAcademia) {
        alert("Por favor, preencha a cidade e o nome da academia.");
        return;
    }

    // Exemplo fixando o estado como SP (ou você pode criar um input para o estado também)
    const resultados = await buscarAcademiasNaRegiao(nomeAcademia, cidade, "SP");

    console.log("Academias encontradas:", resultados);
    if (resultados.length > 0) {
        alert(`Academia encontrada: ${resultados[0].nome}. Coordenadas salvas!`);
        // Aqui você pode guardar os dados da academia selecionada para validar o GPS depois
    }
}