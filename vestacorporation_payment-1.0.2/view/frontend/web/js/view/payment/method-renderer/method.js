/**
 * Vesta JS component
 *
 * @category Vesta
 * @author   Chetu India Team
 */
define(
    [
        'ko',
        'jquery',
        'Magento_Payment/js/view/payment/cc-form',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Ui/js/model/messageList',
        'Vesta_Payment/js/vestatoken-1.0.3'
    ],
    function (
        ko,
        $,
        Component,
        fullScreenLoader,
        globalMessageList,
        vestatoken
    ) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'Vesta_Payment/payment/form',
                isCcFormShown: true,
                isCcvShown: true,
                storedCards: window.checkoutConfig.payment.vesta_payment.storedCards,
                selectedCard: window.checkoutConfig.payment.vesta_payment.selectedCard,
                canSaveCard: window.checkoutConfig.payment.vesta_payment.saveCard,
                vestaTokenApi: window.checkoutConfig.payment.vesta_payment.tokenApi,
                merchant: window.checkoutConfig.payment.vesta_payment.merchantAccountName,
                vestaToken: window.vestatoken,
                cardTokenNumber: null,
                cardLastFour: null
            },

            /**
             * @override
             */
            initObservable: function () {
                this._super()
                    .observe([
                        'vault_is_enabled',
                        'selectedCard',
                        'storedCards',
                    ]);

                this.isCcFormShown = ko.computed(function () {
                    return !this.useVault() ||
                        this.selectedCard() === undefined ||
                        this.selectedCard() == '';
                }, this);
                //init vesta token api
                this.initVestaToken();
                return this;
            },
            /**
             * @override
             */
            getData: function () {
                return {
                    'method': this.item.method,
                    additional_data: {
                        'vault_is_enabled': this.vault_is_enabled(),
                        'cc_type': this.selectedCardType() != '' ? this.selectedCardType() : this.creditCardType(),
                        'cc_exp_year': this.creditCardExpYear(),
                        'cc_exp_month': this.creditCardExpMonth(),
                        'payment_token': this.cardTokenNumber,
                        'cc_number': this.cardLastFour,
                        'cc_cid': this.creditCardVerificationNumber(),
                        'card_id': this.selectedCard()
                    }
                };
            },
            creditCardExpYear: function () {
                console.log("creditCardExpYear validation");
            },
            useVault: function () {
                return this.getStoredCards().length > 0;
            },
            getMailingAddress: function () {
                return window.checkoutConfig.payment.checkmo.mailingAddress;
            },
            setPlaceOrderHandler: function (handler) {
                this.placeOrderHandler = handler;
            },
            context: function () {
                return this;
            },
            isShowLegend: function () {
                return true;
            },
            getCode: function () {
                return 'vesta_payment';
            },
            isActive: function () {
                return true;
            },
            getStoredCards: function () {
                return this.storedCards();
            },
			/**
             * Action to place order
             * @param {String} key
             */
            placeOrderBefore: function (key) {
                var self = this;
                fullScreenLoader.startLoader();
                if (!self.creditCardNumber() || self.creditCardNumber() == null) {
                    self.placeOrder(key);
                    fullScreenLoader.stopLoader(true);
                } else {
                    this.placeOrderWithToken(key);
                }
            },

            /**
             * Show error message
             * @param {String} errorMessage
             */
            showError: function (errorMessage) {
                globalMessageList.addErrorMessage({
                    message: errorMessage
                });
            },

            /**
             * place order with credit card token
             */
            placeOrderWithToken: function (key) {
                var self = this;
                self.vestaToken.getcreditcardtoken({
                    ChargeAccountNumber: self.creditCardNumber(),
                    onSuccess: function (data) {
                        if (self.validateVestaCardToken(data)) {
                            self.cardTokenNumber = data.ChargeAccountNumberToken;
                            self.cardLastFour = data.PaymentDeviceLast4;
                            self.placeOrder(key);
                            fullScreenLoader.stopLoader(true);
                        }
                    },
                    onFailed: function (failure) {
                        console.warn(failure);
                        fullScreenLoader.stopLoader(true);
                        self.showError("Something went wrong! Please contact your service provider");
                    },
                    onInvalidInput: function (failure) {
                        fullScreenLoader.stopLoader(true);
                        console.warn(failure);
                        self.showError("Something went wrong! Please contact your service provider");
                    }
                });
            },

			/**
             * init vesta token
             */
            initVestaToken: function () {
                this.vestaToken.init({
                    ServiceURL: this.vestaTokenApi,
                    AccountName: this.merchant
                });
            },
			/**
             * validate token
             */
            validateVestaCardToken: function (tokenData) {
                if (tokenData && tokenData.ResponseCode == 0) {
                    return true;
                } else {
                    console.warn(tokenData);
                    fullScreenLoader.stopLoader();
                    this.showError("Something went wrong! Please contact your service provider");
                    return false;
                }
            }
        });
    }
);