<?php

namespace Tests\Unit;

use Carbon\Carbon;
use App\User;
use App\Entity\{ Currency, Lot, Money, Wallet };
use App\Request\Contracts\{ AddLotRequest, BuyLotRequest };
use App\Response\Contracts\LotResponse;
use App\Mail\TradeCreated;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Service\Contracts\{
    CurrencyService,
    WalletService,
    MarketService
};
use App\Repository\Contracts\{
    CurrencyRepository,
    UserRepository,
    WalletRepository,
    MoneyRepository,
    TradeRepository,
    LotRepository
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
use Tests\TestCase;

class MarketServiceTest extends TestCase
{
    use RefreshDatabase;
    
    protected $marketService;

    public function setUp()
    {
        parent::setUp();

        $lotRepositoryStub    = $this->createMock(LotRepository::class);
        $tradeRepositoryStub  = $this->createMock(TradeRepository::class);
        $walletRepositoryStub = $this->createMock(WalletRepository::class);
        $moneyRepositoryStub  = $this->createMock(MoneyRepository::class);
        $userRepositoryStub   = $this->createMock(UserRepository::class);

        $lotRepositoryStub->method('getAll')->willReturn([
            factory(Lot::class)-make(),
            factory(Lot::class)-make(),
            factory(Lot::class)-make(),
            factory(Lot::class)-make(),
        ]);

        $tradeRepositoryStub->method('getAll')->willReturn([
            factory(Trade::class)-make(),
            factory(Trade::class)-make(),
            factory(Trade::class)-make(),
            factory(Trade::class)-make(),
        ]);

        $walletRepositoryStub->method('getAll')->willReturn([
            factory(Wallet::class)-make(),
            factory(Wallet::class)-make(),
            factory(Wallet::class)-make(),
            factory(Wallet::class)-make(),
        ]);

        $moneyRepositoryStub->method('getAll')->willReturn([
            factory(Money::class)-make([
                'amount' => 1.0
            ]),
            factory(Money::class)-make([
                'amount' => 1.0
            ]),
            factory(Money::class)-make([
                'amount' => 1.0
            ]),
            factory(Money::class)-make([
                'amount' => 1.0
            ]),
        ]);

        $userRepositoryStub->method('getAll')->willReturn([
            factory(User::class)-make(),
            factory(User::class)-make(),
            factory(User::class)-make(),
            factory(User::class)-make(),
        ]);

        $this->marketService = new MarketService(
            $lotRepositoryStub,
            $tradeRepositoryStub,
            $walletRepositoryStub,
            $moneyRepositoryStub,
            $userRepositoryStub
        );
    }

    public function testAddAlreadyActiveLot()
    {
        $this->expectException(ActiveLotExistsException::class);
        
        $this->marketService->addLot(new AddLotRequest(
            1,
            1,
            now()->timestamp,
            (now()->timestamp + 3600),
            123.45
        ));

        $this->marketService->addLot(new AddLotRequest(
            1,
            1,
            now()->timestamp,
            (now()->timestamp + 3600),
            123.45
        ));
    }

    public function testAddLotWithIncorrectTimeClose()
    {
        $this->expectException(IncorrectTimeCloseException::class);

        $this->marketService->addLot(new AddLotRequest(
            1,
            1,
            now()->timestamp,
            (now()->timestamp - 1),
            123.45
        ));
    }
    
    public function testAddLotWithNegativePrice()
    {
        $this->expectException(IncorrectPriceException::class);

        $this->marketService->addLot(new AddLotRequest(
            1,
            1,
            now()->timestamp,
            (now()->timestamp + 3600),
            -1
        ));
    }

    public function testAddLot()
    {   
        $lot = $this->marketService->addLot(new AddLotRequest(
            1,
            1,
            now()->timestamp,
            (now()->timestamp + 3600),
            123.45
        ));

        $this->assertNotNull($lot);
        $this->assertInstanceOf(Lot::class, $lot);
        $this->assertDatabaseHas('lots', [
            'currency_id'     => 1,
            'seller_id'       => 1,
            'price'           => 123.45
        ]);
    }

    public function testBuyOwnLot()
    {
        $this->expectException(BuyOwnCurrencyException::class);

        $lot = $this->marketService->addLot(new AddLotRequest(
            1,
            1,
            now()->timestamp,
            (now()->timestamp + 3600),
            123.45
        ));

        $this->marketService->buyLot(new BuyLotRequest(1, 1, 1));
    }

    public function testBuyMoreThanExists()
    {
        $this->expectException(IncorrectLotAmountException::class);

        $lot = $this->marketService->addLot(new AddLotRequest(
            1,
            1,
            now()->timestamp,
            (now()->timestamp + 3600),
            123.45
        ));

        $this->marketService->buyLot(new BuyLotRequest(1, 2, 2));
    }

    public function testBuyNegativeAmount()
    {
        $this->expectException(BuyNegativeAmountException::class);

        $lot = $this->marketService->addLot(new AddLotRequest(
            1,
            1,
            now()->timestamp,
            (now()->timestamp + 3600),
            123.45
        ));

        $this->marketService->buyLot(new BuyLotRequest(1, 2, -1));
    }

    public function testBuyInactiveLot()
    {
        $this->expectException(BuyInactiveLotException::class);

        $lot = $this->marketService->addLot(new AddLotRequest(
            1,
            1,
            now()->timestamp,
            (now()->timestamp + 10),
            123.45
        ));

        sleep(11);

        $this->marketService->buyLot(new BuyLotRequest(1, 2, 1));
    }

    public function testBuyLot()
    {
        $lot = $this->marketService->addLot(new AddLotRequest(
            1,
            1,
            now()->timestamp,
            (now()->timestamp + 10),
            123.45
        ));

        $trade = $this->marketService->buyLot(new BuyLotRequest(1, 2, 1));

        $this->assertNotNull($trade);
        $this->assertInstanceOf(Trade::class, $trade);

        Mail::fake();
        Mail::assertSent(TradeCreated::class, 1);

        $this->assertDatabaseHas('trades', [
            'lot_id'  => 1,
            'user_id' => 1,
            'amount'  => 1
        ]);
    }

    public function testGetNonexistentLot()
    {
        $this->expectException(LotDoesNotExistException::class);

        $lot = $this->marketService->getLot(-1);
    }
    
    public function testGetLot()
    {
        $lot = $this->marketService->getLot(1);

        $this->assertNotNull($lot);
        $this->assertInstanceOf(LotResponse::class, $lot);
    }
    
    public function testGetLotList()
    {
        $lotList = $this->marketService->getLotList();

        $this->assertInternalType('array', $lotList);
        $this->assertContainsOnlyInstancesOf(LotResponse::class, $lotList);
    }
}
