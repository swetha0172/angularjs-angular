
//This controller retrieves data from the productsService and associates it with the $scope
//The $scope is ultimately bound to the products view
app.controller('ProductsController', function ($scope, productsService, $http) {

    $scope.newProduct = {};
    $scope.isEditing = false;
    $scope.editingId = null;

    init();

    function init() {
        productsService.getProducts().success(function(data){
            $scope.products = data;
        });
    }

    $scope.startEdit = function (product) {
        $scope.isEditing = true;
        $scope.editingId = product.id;
        $scope.newProduct = {
            name: product.name,
            code: product.code,
            description: product.description,
            price: product.price
        };
    };

    $scope.insertProduct = function () {
        var name = $scope.newProduct.name;
        var code = $scope.newProduct.code;
        var description = $scope.newProduct.description;
        var price = $scope.newProduct.price;

        if ($scope.isEditing) {
            productsService.updateProduct($scope.editingId, name, code, description, price)
                .success(function (data) {
                    // refresh list from backend
                    init();
                    $scope.isEditing = false;
                    $scope.editingId = null;
                    $scope.newProduct = {};
                })
                .error(function (err) {
                    console.log('Update error', err);
                });
        } else {
            productsService.insertProduct(name, code, description, price)
                .success(function (data) {
                    // append or reload; reload to keep consistent with server
                    init();
                    $scope.newProduct = {};
                })
                .error(function (err) {
                    console.log('Insert error', err);
                });
        }
    };

    $scope.deleteProduct = function (id) {
        productsService.deleteProduct(id).success(function(success){

            for (var i = $scope.products.length - 1; i >= 0; i--) {
                if ($scope.products[i].id === id) {
                    $scope.products.splice(i, 1);
                    break;
                }
            }

        });
    };

});