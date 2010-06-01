/*
 * bar_code_line_edit.h
 *
 *  Created on: 29/05/2010
 *      Author: pc
 */

#ifndef BAR_CODE_LINE_EDIT_H_
#define BAR_CODE_LINE_EDIT_H_

#include <QLineEdit>
#include "plugin_widget.h"

class BarCodeLineEdit : public QLineEdit, public PluginWidget
{
public:
	BarCodeLineEdit(QWidget *parent = 0);
	virtual ~BarCodeLineEdit() {};
	void init(const QStringList &argumentNames, const QStringList &argumentValues);
};

#endif /* BAR_CODE_LINE_EDIT_H_ */