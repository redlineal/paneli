<?amghost

namespace Tests\Unit\Http\Middleware;

use Amghost\Http\Middleware\Authenticate;

class AuthenticateTest extends MiddlewareTestCase
{
    /**
     * Test that a logged in user validates correctly.
     */
    public function testLoggedInUser()
    {
        $this->request->shouldReceive('user')->withNoArgs()->once()->andReturn(true);

        $this->getMiddleware()->handle($this->request, $this->getClosureAssertions());
    }

    /**
     * Test that a logged out user results in an exception.
     *
     * @expectedException \Illuminate\Auth\AuthenticationException
     */
    public function testLoggedOutUser()
    {
        $this->request->shouldReceive('user')->withNoArgs()->once()->andReturnNull();

        $this->getMiddleware()->handle($this->request, $this->getClosureAssertions());
    }

    /**
     * Return an instance of the middleware using mocked dependencies.
     *
     * @return \Amghost\Http\Middleware\Authenticate
     */
    private function getMiddleware(): Authenticate
    {
        return new Authenticate();
    }
}
