<?php declare(strict_types=1);

use Burgerbibliothek\ArkManagementTools\Ncda;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class NcdaTest extends TestCase
{
    
    public $checkZone = '13030/xf93gt2';
    public $xdigits = '0123456789bcdfghjkmnpqrstvwxz000';
    
    public static function arkProvider(): array
    {
        return [
            ['36599/50qv116tt6z'],
            ['36599/zr7b8fc7v8b'],
            ['36599/4fccp7833sb'],
            ['36599/5sdxdfd84rs'],
            ['36599/x48t3wjfx2m'],
            ['36599/qqk771h8sf3'],
            ['36599/vr3dbf6w2rk'],
            ['36599/hpt6qb5zdvb'],
            ['36599/n4jc1jwjvb3'],
            ['36599/80nd8q2pzjc'],
            ['36599/gjtbntzfvcg'],
            ['36599/jphffks1b2c'],
            ['36599/544kh3h1b0j'],
            ['36599/ws61jk3mf4q'],
            ['36599/nd61wv5gx3r'],
            ['36599/h0m454h0vq6'],
            ['36599/1k8jwffp4ww'],
            ['36599/rfvgp63rmsb'],
            ['36599/xh5ssbtb612'],
            ['36599/dgk31sdtzp8'],
            ['36599/pj9wvmvptbj'],
            ['36599/s7gzphpmfkv'],
            ['36599/xj1hn0b9mpm'],
            ['36599/vfh9pbnz79j'],
            ['36599/0sbvsjg31wt'],
            ['36599/3fvvrgfv5wg'],
            ['36599/5jjc2hx2x1r'],
            ['36599/rc5dcxcsnsr'],
            ['36599/svknh9tp9d6'],
            ['36599/29qfvff0nx6'],
            ['36599/4s6gwkw9qfr'],
            ['36599/667shhbfz94'],
            ['36599/ssvx6jg7kzk'],
            ['36599/5kjkr90vvfs'],
            ['36599/2vk9q7b6cg7'],
            ['36599/f6zzgxrk9x2'],
            ['36599/9wbfpg6v78x'],
            ['36599/b358gfwx4z8'],
            ['36599/6v548b0wrj1'],
            ['36599/0hppvrzvskj'],
            ['36599/g6nvrbk1xbf'],
            ['36599/7j98z73fg3h'],
            ['36599/hxt3z5zds75'],
            ['36599/p1nj4zzmrzh'],
            ['36599/ctj8jsvg7wb'],
            ['36599/nm97ptp9zt4'],
            ['36599/5txk2nwrncp'],
            ['36599/8wnd4fkpcsh'],
            ['36599/gvp8nfpx9cc'],
        ];
    }

    /**
     * Test NOID CHECK DIGIT ALGORITHM.
     * https://metacpan.org/dist/Noid/view/noid#NOID-CHECK-DIGIT-ALGORITHM
     */
    public function test_noid_check_digit_algortihm_works(): void
    {
        
        $checkdigit = Ncda::calc($this->checkZone, $this->xdigits);
        $this->assertSame('q', $checkdigit, 'NCDA failed.');

        $this->assertTrue(Ncda::verify($this->checkZone.$checkdigit, $this->xdigits), 'Failed to verify that the NCDA is true.');

    }

    #[DataProvider('arkProvider')]
    public function test_verify(string $ark): void
    {
        $this->assertTrue(Ncda::verify($ark, $this->xdigits), 'Failed to verify that the NCDA is true.');
    }

    public function test_noid_check_digit_algortihm_length_exception(): void
    {
        $this->expectException(Exception::class);
        Ncda::calc($this->xdigits, $this->xdigits);
    }

    public function test_noid_check_digit_algortihm_well_formedness(): void
    {
        $this->expectException(Exception::class);
        Ncda::calc($this->checkZone.'-._$@', $this->xdigits);
    }
}
