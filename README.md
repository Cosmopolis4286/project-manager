
### Resumo da arquitetura e estrutura

# Arquitetura do Projeto

- Backend em Laravel 10: lógica de negócio, API e manipulação do banco.
- Frontend em Vue.js 3 com Inertia.js: SPA reativa, comunicação fluida com backend.
- Estilo com Tailwind CSS para rapidez e responsividade.
- Autenticação via Laravel Breeze.
- Banco de dados MySQL rodando em container Docker.
- Todo ambiente containerizado com Docker e Docker Compose para facilitar desenvolvimento e deploy.

## Fluxo principal

1. Usuário interage com frontend Vue.js.
2. Requisições são tratadas pelo backend Laravel.
3. Backend consulta banco MySQL.
4. Frontend atualiza automaticamente usando reatividade e Inertia.js.
5. Dashboard mostra status e indicadores em tempo real.

## Indicador de Saúde do Projeto

- Calcula percentual de tarefas atrasadas.
- Se mais de 20% das tarefas estiverem atrasadas, sinaliza alerta.
- Cálculo otimizado no backend via queries SQL.
- Atualização reativa no frontend para manter dados sempre atualizados.

## Maior Dificuldade Técnica

A maior dificuldade técnica foi implementar o indicador de saúde do projeto, que precisa identificar quando mais de 20% das tarefas estão atrasadas em relação ao prazo. Para isso, criei uma lógica no backend que calcula esse percentual de forma eficiente, utilizando queries otimizadas para buscar as tarefas atrasadas. No frontend, usei propriedades reativas para garantir que o status do projeto se atualize em tempo real, mantendo o dashboard consistente e responsivo.
