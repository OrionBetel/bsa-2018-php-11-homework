<?php

namespace Tests\Unit;

use App\User;
use App\Entity\{ Currency, Lot, Money, Wallet, Trade };
use App\Request\{ AddLot, BuyLot };
use App\Response\CustomLotResponse;
use Illuminate\Support\Facades\Mail;
use App\Mail\TradeCreated;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Service\Contracts\{
    CurrencyService,
    WalletService,
    MarketService
};
use App\Service\HandleMarket;
use App\Repository\Contracts\{
    CurrencyRepository,
    UserRepository,
    WalletRepository,
    MoneyRepository,
    TradeRepository,
    LotRepository
};
use App\Repository\{
    DatabaseCurrencyRepository,
    DatabaseUserRepository,
    DatabaseWalletRepository,
    DatabaseMoneyRepository,
    DatabaseTradeRepository,
    DatabaseLotRepository
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

        $lotRepositoryStub    = $this->createMock(DatabaseLotRepository::class);
        $tradeRepositoryStub  = $this->createMock(DatabaseTradeRepository::class);
        $walletRepositoryStub = $this->createMock(DatabaseWalletRepository::class);
        $moneyRepositoryStub  = $this->createMock(DatabaseMoneyRepository::class);
        $userRepositoryStub   = $this->createMock(DatabaseUserRepository::class);
        $currencyRepositoryStub   = $this->createMock(DatabaseCurrencyRepository::class);

        $currencyRepositoryStub->method('add')->willReturn(
            factory(Currency::class)->create([
                'id' => 1
            ])
        );

        $userRepositoryStub->method('getById')->willReturn(
            factory(User::class)->create([
                'id' => 1
            ])
        );
        
        $lotRepositoryStub->method('findAll')->willReturn([
            factory(Lot::class)->make(),
            factory(Lot::class)->make(),
            factory(Lot::class)->make(),
            factory(Lot::class)->make(),
        ]);

        $lotRepositoryStub->method('add')->willReturn(
            factory(Lot::class)->create([
                'currency_id' => 1,
                'seller_id'   => 1,
                'price'       => 123.45,
            ])
        );
        
        $lotRepositoryStub->method('getById')->will($this->returnValueMap([
            [1, factory(Lot::class)->make([
                'currency_id'     => 1,
                'seller_id'       => 1,
                'price'           => 123.45,
                'date_time_open'  => (now()->timestamp - 1000),
                'date_time_close' => (now()->timestamp - 100)
            ])],
            [2, null],
            [3, factory(Lot::class)->make([
                'currency_id'     => 1,
                'seller_id'       => 1,
                'price'           => 123.45,
            ])],
        ]));

        $lotRepositoryStub->method('findActiveLot')->will($this->onConsecutiveCalls(
            null,
            factory(Lot::class)->make([
                'currency_id' => 1,
                'seller_id'   => 1,
                'price'       => 123.45
            ])
        ));

        $walletRepositoryStub->method('findByUser')->willReturn(
            factory(Wallet::class)->make([
                'id' => 1,
            ])
        );

        $moneyRepositoryStub->method('findByWalletAndCurrency')->willReturn(
            factory(Money::class)->make()
        );

        $tradeRepositoryStub->method('add')->willReturn(
            factory(Trade::class)->make()
        );

        $this->marketService = new HandleMarket(
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
        
        $this->marketService->addLot(new AddLot(
            1,
            1,
            now()->timestamp,
            (now()->timestamp + 3600),
            123.45
        ));

        $this->marketService->addLot(new AddLot(
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

        $this->marketService->addLot(new AddLot(
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

        $this->marketService->addLot(new AddLot(
            1,
            1,
            now()->timestamp,
            (now()->timestamp + 3600),
            -1
        ));
    }

    public function testAddLot()
    {   
        $lot = $this->marketService->addLot(new AddLot(
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

        $lot = $this->marketService->addLot(new AddLot(
            1,
            1,
            now()->timestamp,
            (now()->timestamp + 3600),
            123.45
        ));

        $this->marketService->buyLot(new BuyLot(1, 1, 1));
    }

    public function testBuyMoreThanExists()
    {
        $this->expectException(IncorrectLotAmountException::class);

        $lot = $this->marketService->addLot(new AddLot(
            1,
            1,
            now()->timestamp,
            (now()->timestamp + 3600),
            123.45
        ));

        $this->marketService->buyLot(new BuyLot(2, 1, 1000000000));
    }

    public function testBuyNegativeAmount()
    {
        $this->expectException(BuyNegativeAmountException::class);

        $lot = $this->marketService->addLot(new AddLot(
            1,
            1,
            now()->timestamp,
            (now()->timestamp + 3600),
            123.45
        ));

        $this->marketService->buyLot(new BuyLot(2, 1, -1));
    }

    public function testBuyInactiveLot()
    {
        $this->expectException(BuyInactiveLotException::class);

        $lot = $this->marketService->addLot(new AddLot(
            1,
            1,
            now()->timestamp,
            (now()->timestamp + 5),
            123.45
        ));

        sleep(10);

        $this->marketService->buyLot(new BuyLot(2, 1, 1));
    }

    public function testBuyLot()
    {
        $lot = $this->marketService->addLot(new AddLot(
            1,
            1,
            now()->timestamp,
            (now()->timestamp + 3600),
            123.45
        ));

        $trade = $this->marketService->buyLot(new BuyLot(2, 3, 1));

        $this->assertNotNull($trade);
        $this->assertInstanceOf(Trade::class, $trade);

        Mail::fake();
        Mail::queue(TradeCreated::class);
    }

    public function testGetNonexistentLot()
    {
        $this->marketService->addLot(new AddLot(
            1,
            1,
            now()->timestamp,
            (now()->timestamp + 3600),
            123.45
        ));
        
        $this->expectException(LotDoesNotExistException::class);

        $lot = $this->marketService->getLot(2);
    }
    
    public function testGetLot()
    {
        $this->marketService->addLot(new AddLot(
            1,
            1,
            now()->timestamp,
            (now()->timestamp + 3600),
            123.45
        ));
        
        $lot = $this->marketService->getLot(1);

        $this->assertNotNull($lot);
        $this->assertInstanceOf(CustomLotResponse::class, $lot);
    }
    
    public function testGetLotList()
    {
        $lotList = $this->marketService->getLotList();

        $this->assertInternalType('array', $lotList);
        $this->assertContainsOnlyInstancesOf(CustomLotResponse::class, $lotList);
    }
}
