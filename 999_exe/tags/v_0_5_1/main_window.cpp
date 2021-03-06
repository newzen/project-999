#include "main_window.h"

#include <QMessageBox>
#include <QUrl>
#include <QCloseEvent>
#include <QDesktopServices>
#include "registry.h"
#include "section/main_section.h"
#include "cash_register_dialog/cash_register_dialog.h"
#include "section/sales_section.h"
#include "section/deposit_section.h"
#include "section/cash_register_section.h"
#include "section/working_day_section.h"
#include "../consult_product_dialog/consult_product_dialog.h"
#include "../search_product/search_product_model.h"

/**
 * @class MainWindow
 * Class which handles when and how to display the system sections.
 */

/**
 * Constructs a MainWindow.
 */
MainWindow::MainWindow(QWidget *parent)
    : QMainWindow(parent)
{
	ui.setupUi(this);

	QWebSettings::globalSettings()->
			setAttribute(QWebSettings::PluginsEnabled, true);

	m_IsSessionActive = false;
	m_ServerUrl = Registry::instance()->serverUrl();
	loadMainSection();
}

/**
 * Sets the isSessionActive property to the isActive boolean value.
 * The MainWindow will not close if the session still active.
 */
void MainWindow::setIsSessionActive(bool isActive)
{
	m_IsSessionActive = isActive;
}

/**
 * Loads the MainSection.
 */
void MainWindow::loadMainSection()
{
	menuBar()->clear();

	MainSection *section =
			new MainSection(&m_CookieJar, &m_PluginFactory, m_ServerUrl, this);

	setSection(section);
}

/**
 * Loads the SalesSection.
 */
void MainWindow::loadSalesSection()
{
	CashRegisterDialog dialog(&m_CookieJar, m_ServerUrl, this,
			Qt::WindowTitleHint);

	connect(&dialog, SIGNAL(sessionStatusChanged(bool)), this,
			SLOT(setIsSessionActive(bool)), Qt::QueuedConnection);

	dialog.init();
	if (dialog.exec() == QDialog::Accepted) {
		SalesSection *section = new SalesSection(&m_CookieJar, &m_PluginFactory,
				m_ServerUrl, dialog.key(), this);
		section->setStyleSheetFileName("invoice_details.xsl");
		section->setGetDocumentDetailsCmd("get_invoice_details");
		section->setGetDocumentListCmd("get_invoice_list");
		section->setShowDocumentFormCmd("show_invoice_form");
		section->setGetDocumentCmd("get_invoice");
		section->setCreateDocumentCmd("create_invoice");
		section->setDeleteItemDocumentCmd("delete_product_invoice");

		section->setCreateDocumentTransformerName("invoice");
		section->setDocumentListTransformerName("invoice_list");

		section->setItemsName("Producto");

		section->init();
		setSection(section);
	}
}

/**
 * Loads the DepositSection.
 */
void MainWindow::loadDepositSection()
{
	CashRegisterDialog dialog(&m_CookieJar, m_ServerUrl, this,
			Qt::WindowTitleHint);

	connect(&dialog, SIGNAL(sessionStatusChanged(bool)), this,
			SLOT(setIsSessionActive(bool)), Qt::QueuedConnection);

	dialog.init();
	if (dialog.exec() == QDialog::Accepted) {
		DepositSection *section = new DepositSection(&m_CookieJar, &m_PluginFactory,
				m_ServerUrl, dialog.key(), this);
		section->setStyleSheetFileName("deposit_details.xsl");
		section->setGetDocumentDetailsCmd("get_deposit_details");
		section->setGetDocumentListCmd("get_deposit_list");
		section->setShowDocumentFormCmd("show_deposit_form");
		section->setGetDocumentCmd("get_deposit");
		section->setCreateDocumentCmd("create_deposit");
		section->setDeleteItemDocumentCmd("delete_cash_deposit");

		section->setCreateDocumentTransformerName("deposit");
		section->setDocumentListTransformerName("deposit_list");

		section->setItemsName("Efectivo");

		section->init();
		setSection(section);
	}
}

/**
 * Loads the CashRegisterSection.
 */
void MainWindow::loadCashRegisterSection()
{
	CashRegisterDialog dialog(&m_CookieJar, m_ServerUrl, this,
			Qt::WindowTitleHint);

	connect(&dialog, SIGNAL(sessionStatusChanged(bool)), this,
			SLOT(setIsSessionActive(bool)), Qt::QueuedConnection);

	dialog.init();
	if (dialog.exec() == QDialog::Accepted) {
		CashRegisterSection *section =
				new CashRegisterSection(&m_CookieJar, &m_PluginFactory, m_ServerUrl,
						dialog.key(), this);

		section->setPreliminaryReportName("Corte de caja preliminar");
		section->setReportName("Corte de caja");
		section->setObjectName("Caja");
		section->setCloseMessage("Una vez cerrada no se podra facturar ni crear"
				"depositos.");

		section->init();

		setSection(section);
	}
}

/**
 * Loads the WorkingDaySection.
 */
void MainWindow::loadWorkingDaySection()
{
	WorkingDaySection *section = new WorkingDaySection(&m_CookieJar,
			&m_PluginFactory, m_ServerUrl, this);

	section->setPreliminaryReportName("Reporte de ventas preliminar");
	section->setReportName("Reporte de ventas");
	section->setObjectName("Jornada");
	section->setCloseMessage("Se cerraran todas las cajas en esta jornada.");

	section->init();

	setSection(section);
}

/**
 * Shows the consult product dialog for searching for certain product.
 */
void MainWindow::consultProduct()
{
	SearchProductModel model;

	ConsultProductDialog dialog(&m_CookieJar, m_ServerUrl, &model, this,
			Qt::WindowTitleHint);

	dialog.exec();
}

/**
 * Opens the default browser with the system's help url.
 */
void MainWindow::openHelp()
{
	QDesktopServices::openUrl(*(Registry::instance()->helpUrl()));
}

/**
 * Override closeEvent method for avoiding closing the MainWindow if the session
 * still active.
 */
void MainWindow::closeEvent(QCloseEvent *event)
{
	if (!m_IsSessionActive) {
		event->accept();
	} else {
		QMessageBox::information(this, "Sesi�n Activa", "La sesi�n aun esta "
				"activa, favor de desloguearse del sistema para poder salir.");
		event->ignore();
	}
}

/**
 * Sets the section as the central widget of the MainWindow.
 */
void MainWindow::setSection(Section *section)
{
	connect(section, SIGNAL(sessionStatusChanged(bool)), this,
				SLOT(setIsSessionActive(bool)));

	setCentralWidget(section);
}
