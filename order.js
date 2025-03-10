class Order {
    constructor(renderAddress = false) {
        // 1. Инициализация элементов интерфейса
        this.createOrderButtonID = 'order-modal-button';
        this.selectDealID = 'dealbx24-id';
        this.selectFormOrderID = 'form-order';
        this.selectLoginFormID = 'login-form';
        this.selectAuthModalID = 'authModal';
        this.selectAuthFormUsernameID = 'authform-username';
        this.selectHeadNavModalID = 'lk-header';

        this.dealID = null;
        this.username = null;
        this.needAuth = false;
        this.retryCount = 0; // Счетчик попыток повторного запроса
        this.maxRetries = 3; // Максимальное количество попыток

        // 2. Получаем ссылки на DOM-элементы
        this.selectCreateOrderButton = document.getElementById(this.createOrderButtonID);
        this.dealInput = document.getElementById(this.selectDealID);
        this.selectFormOrder = document.getElementById(this.selectFormOrderID);
        this.selectLoginForm = document.getElementById(this.selectLoginFormID);
        this.selectAuthModal = document.getElementById(this.selectAuthModalID);
        this.selectAuthFormUsername = document.getElementById(this.selectAuthFormUsernameID);
        this.selectHeadNavModal = document.getElementById(this.selectHeadNavModalID);

        // 3. Проверка наличия элементов
        if (!this.selectFormOrder) {
            console.error('Форма не найдена!');
            return;
        }

        this.addressRetryCount = 0; // Новый счетчик для попыток получения адреса
        this.maxAddressRetries = 3; // Максимальное количество попыток
        this.retryDelay = 10000;     // Задержка 10 секунд

        if(renderAddress){
            this.buyOrderModalButtonID = 'buy-order-modal-button';
            this.selectOrderModalID = 'orderModal';
            this.selectBuyOrderModalButton = document.getElementById(this.buyOrderModalButtonID);
            this.selectOrderModal = document.getElementById(this.selectOrderModalID);
            this.getAddress();
        }

        // 4. Инициализация данных формы
        this.updateFormData();

        this.setEvents();
    }

    // 5. Метод для обновления данных формы
    updateFormData() {
        this.formData = new FormData(this.selectFormOrder);
        this.formDataObj = Object.fromEntries(this.formData.entries());
    }

    setEvents() {
        // 6. Проверка существования кнопки
        if (!this.selectCreateOrderButton) {
            console.error('Кнопка создания заказа не найдена!');
            return;
        }

        const createOrderButton = this.getEntity(this.selectCreateOrderButton, 'select-order-button');
        const inputUserName = this.getEntity(this.selectFormOrder, 'select-username');

        if (inputUserName.value !== '') {
            this.username = inputUserName.value;
        }

        inputUserName.addEventListener('change', (e) => {
            this.username = inputUserName.value;
        });

        // 7. Добавление обработчика с правильным контекстом
        createOrderButton.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopImmediatePropagation();
            this.createDeal(e);
        });

        if (this.selectBuyOrderModalButton) {
            const buyOrderModalButton = this.getEntity(this.selectBuyOrderModalButton, 'select-order-buy-button');
            // 7.1 Добавление обработчика с правильным контекстом
            buyOrderModalButton.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopImmediatePropagation();
                this.createOrder(e);
            });
        }

        const selectAuthLink = this.getEntity(this.selectHeadNavModal, 'select-auth-link');

        if (selectAuthLink) {
            // 7.2 Добавление обработчика с правильным контекстом
            selectAuthLink.addEventListener('click', (e) => {
                const inputisNewOrder = this.getEntity(this.selectLoginForm, 'select-is-new-order');

                if(inputisNewOrder){
                    inputisNewOrder.value = false;
                }
            });
        }
    }

    getEntity(parent, entity, all = false) {
        if (!parent || !entity) return null;
        try {
            return all ?
                parent.querySelectorAll(`[data-entity="${entity}"]`) :
                parent.querySelector(`[data-entity="${entity}"]`);
        } catch (error) {
            console.error('Ошибка при поиске элемента:', error);
            return null;
        }
    }

    userNameChange() {
        return this.username !== this.selectAuthFormUsername.value;
    }

    async createDeal(e) {
        this.updateFormData();

        if (this.username === null) {
            let err = {
                "success": false,
                "errors": {
                    "username": [
                        "Номер телефона не должен быть пустым"
                    ]
                }
            }
            // 1. Показываем ошибки у полей
            this.showFormErrors(err.errors);
            // 2. Формируем сообщение для alert
            let errorMessage = this.formatErrorsToMessage(err.errors);
            return this.showAlert(errorMessage);
        }

        if (this.needAuth) {
            return this.SHOW_AUTH_MODAL({ phone: this.username });
        }

        try {
            // Если dealID уже есть, выполняем только действия
            if (this.dealID !== null) {
                return this.SHOW_DEAL_ID({ dealID: this.dealID });
            }

            // Отправка запроса на сервер
            const response = await this.sendCreateDealRequest();
            this.clearErrors();

            // Обработка действий
            await this.executeActions(response);

        } catch (err) {
            this.handleCreateDealError(err);
        }
    }

    async createOrder(e) {
        this.updateFormData();

        if (this.username === null) {
            let err = {
                "success": false,
                "errors": {
                    "username": [
                        "Номер телефона не должен быть пустым"
                    ]
                }
            }
            // 1. Показываем ошибки у полей
            this.showFormErrors(err.errors);
            // 2. Формируем сообщение для alert
            let errorMessage = this.formatErrorsToMessage(err.errors);
            return this.showAlert(errorMessage);
        }

        try {
            // Отправка запроса на сервер
            const response = await this.sendCreateOrderRequest();
            this.clearErrors();

            // Проверяем наличие URL для редиректа
            if (response.success && response.url) {
                // Создаем прелоадер
                const loader = this.createRedirectLoader();
                let seconds = 3;

                // Сохраняем таймер в свойстве класса
                this.redirectTimer = setInterval(() => {
                    loader.querySelector('.redirect-timer').textContent = seconds;
                    seconds--;

                    if (seconds < 0) {
                        this.clearRedirect();
                        window.location.href = response.url;
                    }
                }, 1000);

                return;
            }

            // Если URL нет - продолжаем обычную обработку
            await this.executeActions(response);

        } catch (err) {
            this.handleCreateDealError(err);
        }
    }

    async getAddress() {
        try {
            const response = await this.sendRequestAddress();
            this.addressRetryCount = 0; // Сбрасываем счетчик при успехе
            await this.executeActions(response);
        } catch (err) {
            if (this.addressRetryCount < this.maxAddressRetries) {
                this.addressRetryCount++;
                console.error(`Ошибка получения адреса (попытка ${this.addressRetryCount}/${this.maxAddressRetries}). Повтор через ${this.retryDelay/1000} сек.`, err);

                // Добавляем задержку перед повторной попыткой
                await new Promise(resolve => setTimeout(resolve, this.retryDelay));
                return this.getAddress();
            } else {
                console.error('Превышено максимальное количество попыток получения адреса');
                this.showAlert('Не удалось получить данные адреса. Пожалуйста, попробуйте позже.');
                this.addressRetryCount = 0; // Сбрасываем счетчик
            }
        }
    }

    async sendCreateDealRequest() {
        return new Promise((resolve, reject) => {
            $.ajax({
                url: '/create-deal',
                type: 'POST',
                dataType: "json",
                data: this.formDataObj,
                success: (res) => res.success ? resolve(res) : reject(res),
                error: (xhr) => reject({
                    status: xhr.status,
                    response: xhr.responseJSON || xhr.responseText
                })
            });
        });
    }

    async sendCreateOrderRequest() {
        return new Promise((resolve, reject) => {
            $.ajax({
                url: '/create-order',
                type: 'POST',
                dataType: "json",
                data: this.formDataObj,
                success: (res) => res.success ? resolve(res) : reject(res),
                error: (xhr) => reject({
                    status: xhr.status,
                    response: xhr.responseJSON || xhr.responseText
                })
            });
        });
    }

    async sendRequestAddress() {
        return new Promise((resolve, reject) => {
            $.ajax({
                url: '/get-address-order',
                type: 'GET',
                dataType: "json",
                success: (res) => res.success ? resolve(res) : reject(res),
                error: (xhr) => reject({
                    status: xhr.status,
                    response: xhr.responseJSON || xhr.responseText
                })
            });
        });
    }

    async executeActions(response) {
        if (!response.data?.actions) return;
        for (const action of response.data.actions) {
            try {
                if (typeof this[action] === 'function') {
                    await this[action](response.data);
                } else {
                    console.warn(`Метод ${action} не существует`);
                }
            } catch (error) {
                console.error(`Ошибка в действии ${action}:`, error);

                if (action === 'SHOW_DEAL_ID') {
                    this.dealID = null;
                    throw new Error('ORDER_MODAL_ERROR');
                }

                if (action === 'RENDER_ADDRESS_MODAL') {
                    throw new Error('RENDER_ADDRESS_MODAL_ERROR');
                }

            }
        }
    }

    handleCreateDealError(err) {
        if (err.message === 'ORDER_MODAL_ERROR' && this.retryCount < this.maxRetries) {
            this.retryCount++;
            if (confirm('Ошибка при отображении данных. Повторить попытку?')) {
                return this.createDeal(); // Повторный вызов
            }
            return;
        }

        let errorMessage = 'Неизвестная ошибка';

        if (err.errors) {
            this.showFormErrors(err.errors);
            errorMessage = this.formatErrorsToMessage(err.errors);
        } else if (err.response?.errors) {
            this.showFormErrors(err.response.errors);
            errorMessage = this.formatErrorsToMessage(err.response.errors);
        } else if (err.message) {
            errorMessage = err.message;
        } else if (err.response?.message) {
            errorMessage = err.response.message;
        }

        this.showAlert(errorMessage);
    }

    // Остальные методы остаются без изменений
    formatErrorsToMessage(errors) {
        if (Array.isArray(errors)) {
            return errors.join('\n');
        }

        if (typeof errors === 'object') {
            return Object.values(errors)
                .flatMap(messages => messages)
                .join('\n');
        }

        return 'Произошла ошибка при отправке формы';
    }

    // Показывает alert с сообщением
    showAlert(message) {
        alert(`Ошибка:\n${message}`);
    }

    clearErrors() {
        // Убираем классы ошибок
        document.querySelectorAll('.is-invalid').forEach(el => {
            el.classList.remove('is-invalid');
        });

        // Очищаем сообщения об ошибках
        document.querySelectorAll('.invalid-feedback').forEach(el => {
            el.textContent = '';
        });
    }

    showFormErrors(errors) {
        this.clearErrors();

        // Добавляем проверку типа errors
        if (!errors || typeof errors !== 'object') {
            console.error('Некорректный формат ошибок:', errors);
            return;
        }

        Object.entries(errors).forEach(([field, messages]) => {
            // Исправленный селектор (убраны переносы строк)
            const element = document.querySelector(
                `[name="DealBx24[${field}]"], [data-field="${field}"]`
            );

            if (!element) {
                console.warn(`Элемент для поля ${field} не найден`);
                return;
            }

            // Добавляем класс и текст ошибки
            element.classList.add('is-invalid');

        });
    }

    showGenericError() {
        alert('Произошла ошибка при создании заказа. Пожалуйста, попробуйте еще раз.');
    }

    showModelOrder(){
        if (this.selectOrderModal) {
            this.selectOrderModal.classList.add('show');
            return;
        }
    }

    SHOW_DEAL_ID({ dealID }) {
        if (!dealID) {
            throw new Error('Некорректный идентификатор сделки.');
        }

        this.dealID = dealID;

        if (this.dealInput && this.dealInput.value !== undefined) {
            this.dealInput.value = this.dealID;
            this.showModelOrder();
        }
    }

    SHOW_AUTH_MODAL({ phone }) {
        if (this.selectAuthFormUsername && this.selectAuthFormUsername.value !== undefined) {
            this.selectAuthFormUsername.value = phone;
            this.needAuth = true;
        }

        const inputisNewOrder = this.getEntity(this.selectLoginForm, 'select-is-new-order');

        if(inputisNewOrder){
            inputisNewOrder.value = true;
            this.selectAuthModal.classList.toggle('show');
        }
    }

    RENDER_ADDRESS_MODAL({ addressSelected, addresses, addresses_delivery_time, dates, areaDelivery }) {
        // Проверка входных параметров
        if (!addressSelected || typeof addressSelected !== 'string' || addressSelected.trim() === '') {
            throw new Error('Не выбран адрес доставки');
        }

        if (!addresses || typeof addresses !== 'object' || Object.keys(addresses).length === 0) {
            throw new Error('Нет доступных адресов');
        }

        if (!areaDelivery || typeof areaDelivery !== 'object' || Object.keys(areaDelivery).length === 0) {
            throw new Error('Нет доступных зон доставки');
        }

        if (!addresses_delivery_time || typeof addresses_delivery_time !== 'object' || Object.keys(addresses_delivery_time).length === 0) {
            throw new Error('Нет данных о времени доставки');
        }

        if (!dates || !Array.isArray(dates) || dates.length === 0) {
            throw new Error('Нет доступных дат доставки');
        }

        if (!(addressSelected in addresses)) {
            throw new Error(`Выбранный адрес '${addressSelected}' не найден`);
        }

        if (!(addressSelected in addresses_delivery_time)) {
            throw new Error(`Нет данных о времени доставки для адреса '${addressSelected}'`);
        }

        const isValidDates = dates.every(item =>
            item &&
            typeof item === 'object' &&
            'id' in item &&
            'title' in item &&
            'text' in item
        );

        if (!isValidDates) {
            throw new Error('Некорректные данные о датах доставки');
        }

        const addressMarkup = generateAddressMarkup(addresses, addressSelected, areaDelivery, addresses_delivery_time);
        $('.addressGenerate').append(addressMarkup);
    }

    // Метод для создания прелоадера
    createRedirectLoader() {
        const loaderHTML = `
        <div class="redirect-overlay">
            <div class="redirect-loader">
                <h3>Вас перенаправят на страницу оплаты через <span class="redirect-timer">3</span> секунды</h3>
                <div class="loader"></div>
                <button class="cancel-redirect">Отменить</button>
            </div>
        </div>
    `;

        const loader = document.createElement('div');
        loader.innerHTML = loaderHTML;
        document.body.appendChild(loader);

        // Добавляем обработчик с привязкой контекста
        loader.querySelector('.cancel-redirect').addEventListener('click', () => {
            this.clearRedirect();
        });

        return loader;
    }

    // Новый метод для очистки
    clearRedirect() {
        if (this.redirectTimer) {
            clearInterval(this.redirectTimer);
            this.redirectTimer = null;
        }

        const loader = document.querySelector('.redirect-overlay');
        if (loader) {
            loader.remove();
        }
    }
}