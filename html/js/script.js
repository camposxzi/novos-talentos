
const vagas = [
    {
        id: 1,
        cargo: "Jovem Aprendiz Administrativo",
        empresa: "Empresa demonstrativa A",
        cidade: "Ipatinga, MG",
        tipo: "Jovem Aprendiz",
        salario: "A combinar",
        descricao: "Oportunidade para desenvolver habilidades administrativas e conhecer a rotina de uma empresa."
    },
    {
        id: 2,
        cargo: "Estágio em Tecnologia",
        empresa: "Empresa demonstrativa B",
        cidade: "Ipatinga, MG",
        tipo: "Estágio",
        salario: "A combinar",
        descricao: "Ideal para estudantes interessados em tecnologia, sistemas e desenvolvimento de soluções."
    },
    {
        id: 3,
        cargo: "Auxiliar de Atendimento",
        empresa: "Empresa demonstrativa C",
        cidade: "Coronel Fabriciano, MG",
        tipo: "Primeiro Emprego",
        salario: "A combinar",
        descricao: "Desenvolva habilidades de comunicação, organização e atendimento ao público."
    },
    {
        id: 4,
        cargo: "Assistente Administrativo",
        empresa: "Empresa demonstrativa D",
        cidade: "Timóteo, MG",
        tipo: "Efetivo",
        salario: "A combinar",
        descricao: "Apoio em atividades administrativas, organização de documentos e rotinas de escritório."
    },
    {
        id: 5,
        cargo: "Estágio em Marketing",
        empresa: "Empresa demonstrativa E",
        cidade: "Ipatinga, MG",
        tipo: "Estágio",
        salario: "A combinar",
        descricao: "Participe de atividades de comunicação, criação de conteúdo e divulgação digital."
    },
    {
        id: 6,
        cargo: "Jovem Aprendiz em Logística",
        empresa: "Empresa demonstrativa F",
        cidade: "Timóteo, MG",
        tipo: "Jovem Aprendiz",
        salario: "A combinar",
        descricao: "Aprenda sobre organização, controle de materiais e processos logísticos."
    }
];

const jobsGrid = document.getElementById("jobsGrid");
const searchForm = document.getElementById("searchForm");
const searchInput = document.getElementById("searchInput");
const locationInput = document.getElementById("locationInput");
const typeFilter = document.getElementById("typeFilter");
const resultsCount = document.getElementById("resultsCount");
const emptyState = document.getElementById("emptyState");
const clearFilters = document.getElementById("clearFilters");
const menuToggle = document.getElementById("menuToggle");
const navLinks = document.getElementById("navLinks");

let categoriaSelecionada = "";

function normalizar(texto) {
    return texto
        .normalize("NFD")
        .replace(/[\u0300-\u036f]/g, "")
        .toLowerCase()
        .trim();
}

function criarCard(vaga) {
    const card = document.createElement("article");
    card.className = "job-card";

    const topo = document.createElement("div");
    topo.className = "job-top";

    const icone = document.createElement("span");
    icone.className = "company-icon";
    icone.textContent = vaga.empresa
        .replace("Empresa demonstrativa ", "")
        .slice(0, 2);

    const tipo = document.createElement("span");
    tipo.className = "job-type";
    tipo.textContent = vaga.tipo;

    topo.append(icone, tipo);

    const titulo = document.createElement("h3");
    titulo.textContent = vaga.cargo;

    const empresa = document.createElement("p");
    empresa.className = "company-name";
    empresa.textContent = vaga.empresa;

    const info = document.createElement("div");
    info.className = "job-info";

    const cidade = document.createElement("span");
    cidade.textContent = "📍 " + vaga.cidade;

    const modalidade = document.createElement("span");
    modalidade.textContent = "💼 " + vaga.tipo;

    info.append(cidade, modalidade);

    const descricao = document.createElement("p");
    descricao.className = "job-description";
    descricao.textContent = vaga.descricao;

    const rodape = document.createElement("div");
    rodape.className = "job-bottom";

    const salario = document.createElement("span");
    salario.className = "job-salary";
    salario.textContent = vaga.salario;

    const link = document.createElement("button");
    link.className = "job-link";
    link.type = "button";
    link.textContent = "Ver detalhes →";

    link.addEventListener("click", () => {
        // Página de detalhes será implementada na próxima etapa.
        alert(
            "Vaga demonstrativa: " + vaga.cargo +
            "\nEmpresa: " + vaga.empresa +
            "\nLocal: " + vaga.cidade +
            "\n\nA página completa de detalhes será criada na próxima etapa."
        );
    });

    rodape.append(salario, link);
    card.append(topo, titulo, empresa, info, descricao, rodape);

    return card;
}

function renderizarVagas() {
    const termo = normalizar(searchInput.value);
    const cidade = normalizar(locationInput.value);
    const tipo = typeFilter.value;

    const filtradas = vagas.filter((vaga) => {
        const textoVaga = normalizar(
            `${vaga.cargo} ${vaga.empresa} ${vaga.descricao} ${vaga.tipo}`
        );

        const localVaga = normalizar(vaga.cidade);

        const correspondeTermo =
            !termo || textoVaga.includes(termo);

        const correspondeCidade =
            !cidade || localVaga.includes(cidade);

        const correspondeTipo =
            !tipo || vaga.tipo === tipo;

        const correspondeCategoria =
            !categoriaSelecionada ||
            vaga.tipo === categoriaSelecionada ||
            (
                categoriaSelecionada === "Tecnologia" &&
                normalizar(vaga.cargo + " " + vaga.descricao)
                    .includes("tecnologia")
            );

        return (
            correspondeTermo &&
            correspondeCidade &&
            correspondeTipo &&
            correspondeCategoria
        );
    });

    jobsGrid.replaceChildren();

    filtradas.forEach((vaga) => {
        jobsGrid.appendChild(criarCard(vaga));
    });

    resultsCount.textContent =
        `${filtradas.length} ${
            filtradas.length === 1
                ? "oportunidade encontrada"
                : "oportunidades encontradas"
        }`;

    emptyState.hidden = filtradas.length !== 0;
}

searchForm.addEventListener("submit", (evento) => {
    evento.preventDefault();

    document.getElementById("vagas").scrollIntoView({
        behavior: "smooth"
    });

    renderizarVagas();
});

typeFilter.addEventListener("change", renderizarVagas);

document.querySelectorAll(".category-card").forEach((botao) => {
    botao.addEventListener("click", () => {
        const categoria = botao.dataset.category;

        categoriaSelecionada =
            categoriaSelecionada === categoria ? "" : categoria;

        document.querySelectorAll(".category-card").forEach((item) => {
            item.classList.toggle(
                "active",
                item.dataset.category === categoriaSelecionada
            );
        });

        // Mantém os filtros consistentes com a categoria selecionada.
        typeFilter.value = "";

        renderizarVagas();

        document.getElementById("vagas").scrollIntoView({
            behavior: "smooth"
        });
    });
});

clearFilters.addEventListener("click", () => {
    searchInput.value = "";
    locationInput.value = "";
    typeFilter.value = "";
    categoriaSelecionada = "";

    document.querySelectorAll(".category-card").forEach((botao) => {
        botao.classList.remove("active");
    });

    renderizarVagas();
});

menuToggle.addEventListener("click", () => {
    const aberto = navLinks.classList.toggle("open");

    menuToggle.setAttribute("aria-expanded", String(aberto));
    menuToggle.setAttribute(
        "aria-label",
        aberto ? "Fechar menu" : "Abrir menu"
    );
});

navLinks.querySelectorAll("a").forEach((link) => {
    link.addEventListener("click", () => {
        navLinks.classList.remove("open");
        menuToggle.setAttribute("aria-expanded", "false");
        menuToggle.setAttribute("aria-label", "Abrir menu");
    });
});

document.getElementById("currentYear").textContent =
    new Date().getFullYear();

renderizarVagas();
