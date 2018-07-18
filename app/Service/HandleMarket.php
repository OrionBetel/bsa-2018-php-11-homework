<?php

namespace App\Service;

use App\Service\Contracts\MarketService;

use App\Entity\{ Lot, Trade };
use App\Request\Contracts\{ AddLotRequest, BuyLotRequest };
use App\Response\Contracts\LotResponse;
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

    public function __construct(LotRepository $lotRepo, TradeRepository $tradeRepo)
    {
        $this->lotRepo   = $lotRepo;
        $this->tradeRepo = $tradeRepo;
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
                'The close date and time of your sell session cannot be less then the open date and time'
            );
        }
        
        if ($lotRequest->getPrice() < 0) {
            throw new IncorrectPriceException('Price of lot cannot be negative.');
        }
        
        $lot = new Lot;
        
        $lot->currency_id     = $lotRequest->getCurrencyId();
        $lot->seller_id       = $lotRequest->getSellerId();
        $lot->date_time_open  = $lotRequest->getDateTimeOpen();
        $lot->date_time_close = $lotRequest->getDateTimeClose();
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

    }

    /**
     * Return list of lots.
     *
     * @return LotResponse[]
     */
    public function getLotList() : array
    {

    }
}
