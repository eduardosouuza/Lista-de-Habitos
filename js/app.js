// Aqui declaramos o módulo da aplicação
var app = angular.module("app", ["ngRoute"]);

// Declaramos um value dentro do módulo
app.value("habitos", {
    habitos: []  // Array para armazenar os hábitos
});

// Controller para a página de lista de hábitos
app.controller("listadehabitos", ["$scope", "$http", "habitos", function($scope, $http, habitos) {
    $scope.habitos = habitos.habitos;

    // Se o array de hábitos estiver vazio, busca os valores da API
    if (habitos.habitos.length == 0) {
        $http.get('http://localhost:80/listadehabitos-rest-api/habito.php')
            .then(function(response) {
                var data = response.data;
                for (var indice in data) {
                    habitos.habitos[indice] = data[indice];
                }
            })
            .catch(function(error) {
                console.error('Erro ao buscar hábitos:', error);
            });
    }

    $scope.mostraLista = $scope.habitos.length == 0;

    // Atualiza o status de um hábito para "V" (vencido)
    $scope.vencerHabito = function(habito) {
        var indice = $scope.habitos.indexOf(habito);
        habito.status = "V";
        $http.put('http://localhost:80/listadehabitos-rest-api/habito.php', habito)
            .then(function(response) {
                $scope.habitos[indice] = response.data;
            })
            .catch(function(error) {
                console.error('Erro ao atualizar hábito:', error);
            });
    };

    // Atualiza o status de um hábito para "A" (ativo)
    $scope.retomarHabito = function(habito) {
        var indice = $scope.habitos.indexOf(habito);
        habito.status = "A";
        $http.put('http://localhost:80/listadehabitos-rest-api/habito.php', habito)
            .then(function(response) {
                $scope.habitos[indice] = response.data;
            })
            .catch(function(error) {
                console.error('Erro ao atualizar hábito:', error);
            });
    };

    // Exclui um hábito
    $scope.desistirHabito = function(habito) {
        $http.delete('http://localhost:80/listadehabitos-rest-api/habito.php', { params: { id: habito.id } })
            .then(function(response) {
                var indice = $scope.habitos.indexOf(habito);
                $scope.habitos.splice(indice, 1);
            })
            .catch(function(error) {
                console.error('Erro ao excluir hábito:', error);
            });
    };
}]);

// Controller para a página de inclusão de novos hábitos
app.controller("novohabito", ["$scope", "$http", "habitos", function($scope, $http, habitos) {
    $scope.habitos = habitos.habitos;
    $scope.nome = "";  // Variável para armazenar o nome do novo hábito

    // Insere um novo hábito
    $scope.inserirHabito = function(nome) {
        if (nome === "") return;  // Valida se o nome não está vazio

        $http.post('http://localhost:80/listadehabitos-rest-api/habito.php', { nome: nome })
            .then(function(response) {
                $scope.habitos.push(response.data);
                $scope.nome = "";  // Limpa o campo após a inserção
            })
            .catch(function(error) {
                console.error('Erro ao inserir hábito:', error);
            });
    };
}]);

// Configura as rotas de navegação da aplicação Web
app.config(["$routeProvider", function($routeProvider) {
    $routeProvider
        .when("/listadehabitos", {
            controller: "listadehabitos",
            templateUrl: "partials/listadehabitos.html"
        })
        .when("/novohabito", {
            controller: "novohabito",
            templateUrl: "partials/novohabito.html"
        })
        .otherwise({  // Redireciona para a lista de hábitos por padrão
            redirectTo: "/listadehabitos"
        });
}]);
