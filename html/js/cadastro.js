
const container = document.getElementById('container');

const botaoEntrar = document.getElementById('entrar');
const botaoCadastrar = document.getElementById('cadastrar');

botaoCadastrar.addEventListener('click', () => {
    container.classList.add('painel-ativo');
});

botaoEntrar.addEventListener('click', () => {
    container.classList.remove('painel-ativo');
});
