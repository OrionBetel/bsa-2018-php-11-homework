<?php

namespace App\Service;

use App\Entity\Money;
use App\Entity\Wallet;
use App\Request\Contracts\CreateWalletRequest;
use App\Request\Contracts\MoneyRequest;
use App\Service\Contracts\WalletService;

class HandleWallet implements WalletService
{
    protected $walletRepo;
    protected $moneyRepo;

    public function __construct(WalletRepository $walletRepo, MoneyRepository $moneyRepo)
    {
        $this->walletRepo = $walletRepo;
        $this->moneyRepo  = $moneyRepo;
    }
    
    /**
     * Add wallet to user.
     *
     * @param CreateWalletRequest $walletRequest
     * @return Wallet
     */
    public function addWallet(CreateWalletRequest $walletRequest) : Wallet
    {        
        $userId = $walletRequest->getUserId();
        
        $userWallet = $this->walletRepo->findByUser($userId);
        
        if ($userWallet) {
            return $userWallet;
        }

        $wallet = new Wallet;
        
        $wallet->user_id = $userId;

        return $this->walletRepo->add($wallet);
    }

    /**
     * Add money to a wallet.
     *
     * @return Money
     */
    public function addMoney(MoneyRequest $moneyRequest) : Money
    {
        $walletId   = $moneyRequest->getWalletId();
        $currencyId = $moneyRequest->getCurrencyId();

        $money = $this->moneyRepo->findByWalletAndCurrency($walletId, $currencyId);
        
        if ($money) {
            return $money;
        }
        
        $money = new Money;

        $money->wallet_id   = $walletId;
        $money->currency_id = $currencyId;
        $money->amount      = $moneyRequest->getAmount();

        return $this->moneyRepo->save($money);
    }

    /**
     * Take money from a wallet.
     *
     * @param MoneyRequest $moneyRequest
     * @return Money
     */
    public function takeMoney(MoneyRequest $moneyRequest) : Money
    {
        $money = $this->moneyRepo->findByWalletAndCurrency(
            $moneyRequest->getWalletId,
            $moneyRequest->getCurrencyId
        );

        $subtractMoneyAmount = $moneyRequest->getAmount();

        if ($subtractMoneyAmount > $money->amount) {
            return $money;
        }
        
        $money->amount -= $subtractMoneyAmount;

        return $this->moneyRepo->save($money);
    }
}
