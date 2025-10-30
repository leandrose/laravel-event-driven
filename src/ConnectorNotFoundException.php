<?php

namespace LeandroSe\LaravelEventDriven;

/**
 * Raised when the manager cannot resolve a connector for the requested configuration name.
 */
class ConnectorNotFoundException extends EventDrivenException
{
}
