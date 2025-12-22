    <script>
        // Função para formatar CNPJ
        function formatCNPJ(value) {
            return value.replace(/\D/g, '')
                .replace(/^(\d{2})(\d)/, '$1.$2')
                .replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3')
                .replace(/\.(\d{3})(\d)/, '.$1/$2')
                .replace(/(\d{4})(\d)/, '$1-$2')
                .substring(0, 18);
        }

        // Função para formatar CPF
        function formatCPF(value) {
            return value.replace(/\D/g, '')
                .replace(/^(\d{3})(\d)/, '$1.$2')
                .replace(/^(\d{3})\.(\d{3})(\d)/, '$1.$2.$3')
                .replace(/\.(\d{3})(\d)/, '.$1-$2')
                .substring(0, 14);
        }

        // Função para formatar telefone
        function formatTelefone(value) {
            value = value.replace(/\D/g, '');
            if (value.length <= 10) {
                return value.replace(/^(\d{2})(\d{4})(\d{4})/, '($1) $2-$3');
            } else {
                return value.replace(/^(\d{2})(\d{5})(\d{4})/, '($1) $2-$3').substring(0, 15);
            }
        }

        // Função para formatar CEP
        function formatCEP(value) {
            return value.replace(/\D/g, '')
                .replace(/^(\d{5})(\d)/, '$1-$2')
                .substring(0, 9);
        }

        // Função para formatar moeda
        function formatMoeda(value) {
            value = value.replace(/\D/g, '');
            value = (parseInt(value) / 100).toFixed(2);
            return value.replace('.', ',').replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1.');
        }

        // Função para confirmar exclusão
        function confirmarExclusao(mensagem) {
            return confirm(mensagem || 'Tem certeza que deseja excluir este registro?');
        }

        // Função para exibir mensagem de sucesso
        function mostrarSucesso(mensagem) {
            const div = document.createElement('div');
            div.className = 'alert alert-success';
            div.textContent = mensagem;
            document.querySelector('.container').prepend(div);
            setTimeout(() => div.remove(), 5000);
        }

        // Função para exibir mensagem de erro
        function mostrarErro(mensagem) {
            const div = document.createElement('div');
            div.className = 'alert alert-error';
            div.textContent = mensagem;
            document.querySelector('.container').prepend(div);
            setTimeout(() => div.remove(), 5000);
        }
    </script>
</body>
</html>
