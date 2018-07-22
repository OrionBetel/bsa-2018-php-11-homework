<?php

namespace App\Service;

use App\Service\Contracts\MarketService;

use Carbon\Carbon;
use App\Entity\{ Lot, Trade };
use App\Request\Contracts\{ AddLotRequest, BuyLotRequest };
use App\Response\Contracts\LotResponse;
use App\Response\CustomLotResponse;
use App\Jobs\SendEmail;
use App\Repository\Contracts\{
    LotRepository,
    TradeRepository,
    WalletRepository,
    MoneyRepository,
    UserRepository
};
use App\Exceptions\MarketException\{
    ActiveLotExistsException,
    IncorrectPriceException,
    IncorrectTimeCloseException,
    BuyOwnCurrencyException,
    IncorrectLotAmountException,
    BuyNegativeAmountException,
    BuyInactiveLotException,
    LotDoesNotExistException
};

class HandleMarket implements MarketService
{
    protected $lotRepo;
    protected $tradeRepo;
    protected $walletRepo;
    protected $moneyRepo;
    protected $userRepo;

    public function __construct(
        LotRepository $lotRepo,
        TradeRepository $tradeRepo,
        WalletRepository $walletRepo,
        MoneyRepository $moneyRepo,
        UserRepository $userRepo
    )
    {
        $this->lotRepo    = $lotRepo;
        $this->tradeRepo  = $tradeRepo;
        $this->walletRepo = $walletRepo;
        $this->moneyRepo  = $moneyRepo;
        $this->userRepo   = $userRepo;
    }
    
    /**
     * Sell currency.
     *
     * @param AddLotRequest $lotRequest
     * 
     * @throws ActiveLotExistsException
     * @throws IncorrectTimeCloseException
     * @throws IncorrectPriceException
     *
     * @return Lot
     */
    public function addLot(AddLotRequest $lotRequest) : Lot
    {
        $activeLot = $this->lotRepo->findActiveLot($lotRequest->getSellerId());
        
        if ($activeLot && $activeLot->currency_id == $lotRequest->getCurrencyId()) {
            throw new ActiveLotExistsException(
                'You cannot have more than one active sell session of a particular currency.'
            );
        }
        
        if ($lotRequest->getDateTimeClose() < $lotRequest->getDateTimeOpen()) {
            throw new IncorrectTimeCloseException(
                'The close date and time of your sell session cannot be less then the open date and time.'
            );
        }
        
        if ($lotRequest->getPrice() < 0) {
            throw new IncorrectPriceException('Lot price cannot be negative.');
        }
        
        $lot = new Lot;
        
        $lot->currency_id     = $lotRequest->getCurrencyId();
        $lot->seller_id       = $lotRequest->getSellerId();
        $lot->date_time_open  = Carbon::createFromTimestamp($lotRequest->getDateTimeOpen())->toDateTimeString();
        $lot->date_time_close = Carbon::createFromTimestamp($lotRequest->getDateTimeClose())->toDateTimeString();
        $lot->price           = $lotRequest->getPrice();

        return $this->lotRepo->add($lot);
    }

    /**
     * Buy currency.
     *
     * @param BuyLotRequest $lotRequest
     * 
     * @throws BuyOwnCurrencyException
     * @throws IncorrectLotAmountException
     * @throws BuyNegativeAmountException
     * @throws BuyInactiveLotException
     * 
     * @return Trade
     */
    public function buyLot(BuyLotRequest $lotRequest) : Trade
    {
        $lot = $this->lotRepo->getById($lotRequest->getLotId());

        $sellerWallet = $this->walletRepo->findByUser($lot->seller_id);
        $sellerMoney = $this->moneyRepo->findByWalletAndCurrency($sellerWallet->id, $lot->currency_id);
        
        $buyerWallet = $this->walletRepo->findByUser($lotRequest->getUserId());
        $buyerMoney = $this->moneyRepo->findByWalletAndCurrency($buyerWallet->id, $lot->currency_id);
        
        if ($lot && $lot->seller_id == $lotRequest->getUserId()) {
            throw new BuyOwnCurrencyException('You cannot buy your own lots.');
        }

        if ($lotRequest->getAmount() > $sellerMoney->amount) {
            throw new IncorrectLotAmountException('You cannot buy more currency than lot contains.');
        }

        if ($lotRequest->getAmount() < 1) {
            throw new BuyNegativeAmountException('You must buy at least one unit of currency.');
        }
        
        if (now()->timestamp > $lot->getDateTimeClose()) {
            throw new BuyInactiveLotException('You cannot buy from a closed lot.');
        }

        $sellerMoney->amount -= $lotRequest->getAmount();
        $this->moneyRepo->save($sellerMoney);

        $buyerMoney->amount += $lotRequest->getAmount();
        $this->moneyRepo->save($buyerMoney);

        $trade = new Trade;
        $trade->lot_id  = $lotRequest->getLotId();
        $trade->user_id = $lotRequest->getUserId();
        $trade->amount  = $lotRequest->getAmount();

        $seller = $this->userRepo->getById($lot->seller_id);

        $savedTrade = $this->tradeRepo->add($trade);
        
        SendEmail::dispatch($savedTrade, $seller);
        
        return $savedTrade;
    }

    /**
     * Retrieves lot by an identifier and returns it in LotResponse format
     *
     * @param int $id
     * 
     * @throws LotDoesNotExistException
     * 
     * @return LotResponse
     */
    public function getLot(int $id) : LotResponse
    {
        $lot = $this->lotRepo->getById($id);

        if (is_null($lot)) {
            throw new LotDoesNotExistException('The requested lot does not exist.');
        }

        return new CustomLotResponse($lot, $this->walletRepo, $this->moneyRepo);
    }

    /**
     * Return list of lots.
     *
     * @return LotResponse[]
     */
    public function getLotList() : array
    {
        $lots = [];

        foreach ($this->lotRepo->findAll() as $lot) {
            $lots[] = new CustomLotResponse($lot, $this->walletRepo, $this->moneyRepo);
        }

        return $lots;
    }
}
