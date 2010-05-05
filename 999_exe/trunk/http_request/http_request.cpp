/*
 * httprequest.cpp
 *
 *  Created on: 11/03/2010
 *      Author: pc
 */

#include "http_request.h"
#include <QEventLoop>

/**
 * @class HttpRequest
 * Handles synchronous or asynchronous communication with the server.
 */

/**
 * Constructs a HtttpRequest with the QNetworkAccessManager and parent.
 */
HttpRequest::HttpRequest(QNetworkAccessManager *manager, QObject *parent)
		: QObject(parent), m_Manager(manager)
{
}

/**
 * Gets the information from the server.
 */
QString HttpRequest::get(QUrl url, ConnectionType type)
{
	if (type == Sync) {
		QEventLoop loop;

		QNetworkReply *reply = m_Manager->get(QNetworkRequest(url));

		connect(reply, SIGNAL(finished()), &loop, SLOT(quit()));

		loop.exec();
		return reply->readAll();
	} else {
		connect(m_Manager, SIGNAL(finished(QNetworkReply*)), this,
				SLOT(loadFinished(QNetworkReply*)));

		m_Manager->get(QNetworkRequest(url));
	}
}

/**
 * Handles the response in case the communication was made asynchronously.
 * Emits the finished signal.
 */
void HttpRequest::loadFinished(QNetworkReply *reply)
{
	emit finished(reply->readAll());
}